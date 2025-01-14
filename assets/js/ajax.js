jQuery(function ($) {

    TSAjax = function (endpoint_url) {

        var _this = this;
        this.endpoint_url = endpoint_url;
        this.requests = new Array();


        this.call = function (query_string, scrollto, total_result_span_id, callback, target) {
            this.oneByOneRequest();
            this.requests.push($.ajax({
                url: this.endpoint_url + '?' + query_string,
                method: 'GET',
                data: null
            }).done(function (data) {
                callback(data, query_string, scrollto, total_result_span_id, target);
            }));
        }

        this.oneByOneRequest = function (){
            for (let i = 0; i < this.requests.length; i++) {
                this.requests[i].abort();
            }
        }

        this.setSpinner = function (search_result) {
            $('.spinner').show();
            $(search_result).html('');
            $('.pagination').hide();
        }

        this.removeSpinner = function () {
            $('.spinner').hide();
            $('.pagination').show();
        }

        this.setButtonLoader = function (btn) {
            btn.find('svg').hide();
            btn.find('span').hide();
            btn.find('.loader').show();
        }

        this.removeButtonLoader = function (btn) {
            btn.find('svg').show();
            btn.find('span').show();
            btn.find('.loader').hide();
        }

        this.resultHandlerWishlist = function (data) {

            // set the wishlist
            for (var key in data.html) {
                $('#' + key).html(data.html[key]);
            }

            // sync results to localstorage (if object are deleted from server)
            let wishlist = JSON.parse(window.localStorage.getItem('wishlist'));
            if (!jQuery.isEmptyObject(wishlist)) {
                $(wishlist).each(function (key, item) {
                    var is_valid = false;
                    $(data.ids).each(function (key, id) {
                        if (item['pm-id'] == id) {
                            is_valid = true;
                        }
                    });
                    if (!is_valid) {
                        console.log(' not valid remove' + item['pm-id']);
                        _this.wishlistRemoveElement(wishlist, item['pm-id']);
                    }
                });
                $('.wishlist-count').text(wishlist.length);
                window.localStorage.setItem('wishlist', JSON.stringify(wishlist));
            }

        }

        this.resultHandlerSearch = function (data, query_string, scrollto, total_result_span_id) {

            _this.removeSpinner();

            for (let key in data.html) {
                if (key == 'search-result') {
                    $('#' + key).html(data.html[key]).find('.content-block-travel-cols').fadeIn()
                        .css({top:1000,position:'relative'})
                        .animate({top:0}, 80, 'swing')
                }else{
                    $('#' + key).html(data.html[key]);
                }

                if (key == 'search-filter' && $('.js-range-slider').length > 0) {
                    $(".js-range-slider").ionRangeSlider({});
                }
            }

            if (total_result_span_id != null) {
                let total_count_span = $(total_result_span_id);
                let str = '';
                if (data.count == 1) {
                    str = data.count + ' ' + total_count_span.data('total-count-singular');
                } else if (data.count > 1 || data.count == 0) {
                    str = data.count + ' ' + total_count_span.data('total-count-plural');
                } else {
                    str = data.count + ' ' + total_count_span.data('total-count-default');
                }
                total_count_span.html(str.trim());
            }

            if (scrollto != null) {
                _this.scrollTo(scrollto);
            }

            window.history.pushState(null, '', window.location.pathname + '?' + query_string);

        }

        this.scrollTo = function (scrollto) {
            $('html, body').stop().animate({
                'scrollTop': $(scrollto).offset().top - $('header.affix').height()
            }, 150, 'swing');
        }

        this.resultHandlerSearchBarStandalone = function(data, query_string, scrollto, btn){

            _this.removeButtonLoader(btn)
            let total_count_span = btn.find('span');
            let str = '';
            if (data.count == 1) {
                str = data.count + ' ' + total_count_span.data('total-count-singular');
            } else if (data.count > 1 || data.count == 0) {
                str = data.count + ' ' + total_count_span.data('total-count-plural');
            } else {
                str = data.count + ' ' + total_count_span.data('total-count-default');
            }
            total_count_span.html(str.trim());
        }

        this.renderWishlist = function() {

            let wishlist = JSON.parse(window.localStorage.getItem('wishlist'));
            if (wishlist !== null && wishlist.length !== 0) {
                let query_string = 'action=wishlist&view=Teaser2&pm-id=';
                $('.wishlist-count').text(wishlist.length);
                $('.wishlist-toggler').addClass('animate');
                setTimeout(function () {
                    $('.wishlist-toggler').removeClass('animate');
                }, 1250);
                wishlist.forEach(function (item, key) {
                    if (key !== wishlist.length - 1) {
                        query_string += item['pm-id'] + ',';
                    } else {
                        query_string += item['pm-id'];
                    }
                });
                _this.call(query_string, null, null, _this.resultHandlerWishlist);
            } else {
                _this.wishlistEventListeners();
                $('.wishlist-count').text(0);
                $('.wishlist-items').html(`<p>Keine Reisen auf der Merkliste</p>`);
            }
        }

        this.wishlistEventListeners = function () {

            if ($('#search-result').length > 0) {
                // Create an observer instance linked to the callback function
                var observer = new MutationObserver(function () {
                    _this.wishListInit();
                });

                observer.observe(document.getElementById('search-result'), {attributes: true, childList: true});
            }

            $('body').on('click', '.remove-from-wishlist', function(e) {
                let wishlist = JSON.parse(window.localStorage.getItem('wishlist'));
                if (!jQuery.isEmptyObject(wishlist)) {
                    if (wishlist.some(wi => wi['pm-id'] == $(e.target).data('pm-id'))) {
                        _this.wishlistRemoveElement(wishlist, $(e.target).data('pm-id'));
                        // $('.wishlist-heart').removeClass('active');
                        $('.add-to-wishlist').each(function (key, item) {
                            if ($(item).data('pm-id') == $(e.target).data('pm-id')) {
                                $(item).removeClass('active');
                            }
                        });
                    }
                }
                window.localStorage.setItem('wishlist', JSON.stringify(wishlist));
                _this.renderWishlist();
            });
            if ($('.add-to-wishlist').length > 0) {
                let wishlist = JSON.parse(window.localStorage.getItem('wishlist'));
                $('body').on('click', '.add-to-wishlist', function (e) {
                    if (jQuery.isEmptyObject(wishlist)) {
                        wishlist = [];
                    }
                    if (wishlist.some(wi => wi['pm-id'] == $(e.target).data('pm-id'))) {
                        _this.wishlistRemoveElement(wishlist, $(e.target).data('pm-id'));
                        $(e.target).removeClass('active');
                    } else {
                        wishlist.push({
                            'pm-ot': $(e.target).data('pm-ot'),
                            'pm-id': $(e.target).data('pm-id'),
                            'pm-dr': $(e.target).data('pm-dr'),
                            'pm-du': $(e.target).data('pm-du')
                        });
                        $(e.target).addClass('active');
                    }
                    window.localStorage.setItem('wishlist', JSON.stringify(wishlist));
                    _this.renderWishlist();
                });
            }
        }

        this.wishListInit = function () {
            let wishlist = JSON.parse(window.localStorage.getItem('wishlist'));
            if (!jQuery.isEmptyObject(wishlist)) {
                $('.add-to-wishlist').each(function (key, item) {
                    if (wishlist.some(wi => wi['pm-id'] == $(item).data('pm-id'))) {
                        $(item).addClass('active');
                    }
                });
            }
        }

        this.wishlistRemoveElement = function (array, elem) {
            array.some(function (item) {
                if (item['pm-id'] == elem) {
                    var index = array.indexOf(item);
                    array.splice(index, 1);
                }
            });
        }

        this.pagination = function () {

            $("#search-result").on('click', ".page-link", function (e) {
                var href = $(this).attr('href').split('?');
                var query_string = href[1];

                _this.scrollTo('#search-result');
                _this.setSpinner('#pm-search-result');
                _this.call(query_string, null, null, _this.resultHandlerSearch);
                e.preventDefault();
            });

        }

        this.buildSearchQuery = function (form) {

            let query = [];
            query.push('action=search');

            // the object type
            let id_object_type = $(form).find('input[name=pm-ot]').val();
            if (id_object_type && id_object_type != '') {
                query.push('pm-ot=' + id_object_type);
            }

            // checkboxes
            let selected = [];
            $(form).find('.category-tree input:checked').each(function () {

                let id_parent = $(this).data('id-parent');
                let id = $(this).data('id');
                let name = $(this).data('name');

                if (!selected[name]) {
                    selected[name] = [];
                }

                let i = selected[name].indexOf(id_parent);
                if (i > -1) {
                    // remove if parent is set
                    selected[name].splice(i, 1);
                }

                i = selected[name].indexOf(id);
                if (i == -1) {
                    // has no parent, add
                    selected[name].push(id);
                }

            });

            let key;
            let delimiter = ',';
            for (key in selected) {
                if ($('input[name='+key+'-behavior]').val() == 'AND'){
                    delimiter = '%2B';
                }else{
                    delimiter = ',';
                }
                query.push('pm-c[' + key + ']=' + selected[key].join(delimiter));
            }

            // check and set price-range
            let price_range = $(form).find('input[name=pm-pr]').val();
            let price_mm_range = $(form).find('input[name=pm-pr]').data('min') + '-' + $(form).find('input[name=pm-pr]').data('max');
            if (price_range && price_mm_range != price_range && price_range != '') {
                query.push('pm-pr=' + price_range);
            }

            // check and set duration-range
            let duration_range = $(form).find('select[name=pm-du]').val();
            if (duration_range && duration_range != '') {
                query.push('pm-du=' + duration_range);
            }

            // check and set date-range
            let date_range = $(form).find('input[name=pm-dr]').data('value');
            if (date_range && date_range != '') {
                query.push('pm-dr=' + date_range);
            }

            // check and set search term
            let search_term = $(form).find('input[name=pm-t]').val();
            if (search_term && search_term != '') {
                query.push('pm-t=' + search_term);
            }

            let order = $(form).find('select[name=pm-o]').val();
            if (order && order != '') {
                query.push('pm-o=' + order);
            }

            // the view
            let view = $('.pm-switch-result-view .pm-switch-checkbox').prop('checked');
            if (view) {
                query.push('view=' + $('.pm-switch-result-view .pm-switch-checkbox').val());
            }

            // Build the Query
            let query_string;
            query_string = query.join('&');

            return query_string;
        }

        this.filter = function () {

            // dont run default realtime ajax-functions on small viewport
            if ($(window).width() > 768) {
                $("#search-filter").on('change', ".list-filter-box input, .list-filter-box select", function (e) {
                    var form = $(this).closest('form');

                    // if the second level has no more selected items, we fall back to the parents value
                    if($(this).closest('.form-check.has-second-level').find('input:checked').length == 0){
                        $(this).closest('.form-check.has-second-level').find('input:disabled:first').attr("disabled", false).prop('checked', true);
                    }

                    var query_string = _this.buildSearchQuery(form);
                    _this.setSpinner('#pm-search-result');
                    _this.call(query_string, '#search-result', null, _this.resultHandlerSearch);
                    e.preventDefault();
                });
            }

            $("#search-filter").on('click', ".list-filter-box-submit", function (e) {
                var form = $(this).closest('form');

                // if the second level has no more selected items, we fall back to the parents value
                if($(this).closest('.form-check.has-second-level').find('input:checked').length == 0){
                    $(this).closest('.form-check.has-second-level').find('input:disabled:first').attr("disabled", false).prop('checked', true);
                }

                var query_string = _this.buildSearchQuery(form);
                _this.setSpinner('#pm-search-result');
                _this.call(query_string, '#search-result', null, _this.resultHandlerSearch);
                e.preventDefault();
            });

        }

        this.searchbox = function () {

            /**
             * This Event checks if a input field is modified and building the query string.
             * The query string is added to the form > a.btn > href
             * If the search box is on the same site as the search result, than the ajax search query is fired
             */
            $('#main-search').on('change', '.search-box input, .search-box select', function (e) {

                let form = $(this).closest('form');

                // build the query string and set him on the search button
                let query_string = _this.buildSearchQuery(form);

                let button = $(form).find('a.btn');
                let href = button.attr('href').split('?');
                button.attr('href', href[0] + '?' + query_string);

                // if we're on the same page, let fire the search and set the search results
                let current_location = window.location.href.split('?');
                if (current_location[0] == href[0]) {
                    _this.setSpinner('#pm-search-result');
                    _this.call(query_string, null, button, _this.resultHandlerSearch);
                } else {
                    _this.setButtonLoader(button);
                    // in this case we have placed a search box on a site without a direct result output
                    _this.call(query_string, null, button, _this.resultHandlerSearchBarStandalone);
                }

                e.preventDefault();
            });

        }

        this.searchboxSwitch = function (){
            $(".search-wrapper--tabs_btn").on('click', function (e) {
                $(this).parents().find(".search-wrapper--tabs_btn").toggleClass('is--active');

                let query_string = 'action=searchbar&pm-ot='+$(this).data('pm-ot');
                _this.call(query_string, null, null, _this.resultHandlerSearchBar);

            });
        }

        this.resultHandlerSearchBar = function(data){

            for (var key in data.html) {
                $('#' + key).html(data.html[key]);
            }

            _this.autoCompleteInit();
            _this.dateRangePickerInit();
            _this.initCategoryTreeSearchBarFields();
        }

        this.autoCompleteInit = function (){
            if ($('.auto-complete').length > 0) {
                $('.auto-complete').autocomplete({
                    serviceUrl: '/wp-content/themes/travelshop/pm-ajax-endpoint.php?action=autocomplete',
                    type: 'get',
                    dataType: 'json',
                    paramName: 'q',
                    deferRequestBy: 0,
                    minChars: 2,
                    width: 'flex',
                    groupBy: 'category',
                    preventBadQueries: false,
                    tabDisabled: true,
                    preserveInput: true,
                    onSelect: function (suggestion) {
                        if (suggestion.data.type == 'link') {
                            document.location.href = suggestion.data.url;
                        } else if (suggestion.data.type == 'search') {
                            var url = $(this).parents('form').attr('action');
                            url += '?' + suggestion.data.search_request;
                            document.location.href = url;
                        }
                    }
                })
            }
        }

        this.dateRangePickerInit = function (){
            if ($('[data-type="daterange"]').length > 0) {

                let easterDate = _this.theEasterDate(dayjs().year());
                if(easterDate.isBefore(dayjs())){
                    easterDate = _this.theEasterDate(dayjs().add(1, 'year').year());
                }

                let pfingstenDate = _this.theEasterDate(dayjs().year()).add(49, 'days');
                if(pfingstenDate.isBefore(dayjs())){
                    pfingstenDate = _this.theEasterDate(dayjs().add(1, 'year').year()).add(49, 'days');
                }

                let rosenmontagDate = _this.theEasterDate(dayjs().year()).subtract(48, 'days');
                if(rosenmontagDate.isBefore(dayjs())){
                    rosenmontagDate = _this.theEasterDate(dayjs().add(1, 'year').year()).subtract(48, 'days');
                }


                let picker = $('[data-type="daterange"]').daterangepicker({
                    "ranges": {
                        'Heute': [dayjs(), dayjs()],
                        'Abreise in 30 Tagen': [dayjs().add(30, 'days'), dayjs().add(1, 'month')],
                        'Abreise in 60 Tagen': [dayjs().add(60, 'days'), dayjs().add(1, 'month')],
                        'in diesem Monat': [dayjs().startOf('month'), dayjs().endOf('month')],
                        'über Rosenmontag': [rosenmontagDate.subtract(7, 'days'), rosenmontagDate],
                        'über Ostern': [easterDate.subtract(7, 'days'), easterDate],
                        'über Pfingsten': [pfingstenDate.subtract(7, 'days'), pfingstenDate],
                        'über Weihnachten': [dayjs().date(15).month(11), dayjs().date(24).month(11)],
                        'über Silvester': [dayjs().date(25).month(11), dayjs().date(31).month(11)],
                        'im nächsten Monat': [dayjs().add(1, 'month').startOf('month'), dayjs().add(1, 'month').endOf('month')],
                    },
                    "showWeekNumbers": false,
                    "autoUpdateInput": false,
                    "alwaysShowCalendars": true,
                    "showDropdowns": true,
                    "minDate": $('[data-type="daterange"]').data('mindate'),
                    "maxDate": $('[data-type="daterange"]').data('maxdate'),
                    "showCustomRangeLabel": false,
                    isCustomDate: function(date) {
                        if($('[data-type="daterange"]').data('departures').indexOf(date.format('YYYY-MM-DD')) >= 0){
                            return 'has_departures';
                        }
                        },
                    // "autoApply": true,
                    "locale": {
                        "format": "DD.MM.YYYY",
                        "separator": " - ",
                        "applyLabel": "Auswahl übernehmen",
                        "fromLabel": "Von",
                        "toLabel": "Bis",
                        "customRangeLabel": "Custom",
                        "weekLabel": "W",
                        "daysOfWeek": [
                            "So",
                            "Mo",
                            "Di",
                            "Mi",
                            "Do",
                            "Fr",
                            "Sa"
                        ],
                        "monthNames": [
                            "Januar",
                            "Februar",
                            "März",
                            "April",
                            "Mai",
                            "Juni",
                            "Juli",
                            "August",
                            "September",
                            "Oktober",
                            "November",
                            "Dezember"
                        ],

                        "firstDay": 1,
                        "buttonClasses": "btn btn-outline-secondary btn-block",
                        "applyButtonClasses": "btn btn-outline-secondary btn-block",
                        "cancelClass": "btn-default",
                        "cancelLabel": '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="30" height="30" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2c3e50" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>'
                    },
                    /*
                    "startDate": dayjs().startOf('hour'),
                    "endDate": dayjs().startOf('hour').add(64, 'hour')
                    */
                }, function (start, end, label) {
                    if ($(window).width() <= 767) {
                        $([document.documentElement, document.body]).animate({
                            scrollTop: $('.travelshop-datepicker').offset().top - 83
                        }, 500);
                    }
                    //console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
                });


                $('[data-type="daterange"]').on('apply.daterangepicker', function (ev, picker) {

                    $(this).val(picker.startDate.format('DD.MM.') + ' - ' + picker.endDate.format('DD.MM.YYYY'));

                    // build the a pm ready query string
                    $(this).data('value', picker.startDate.format('YYYYMMDD') + '-' + picker.endDate.format('YYYYMMDD'));

                    $(this).trigger('change');
                });


                $('[data-type="daterange"]').on('cancel.daterangepicker', function (ev, picker) {
                    $(this).val('');
                    $(this).data('value', '');
                    $(this).trigger('change');
                });

                document.addEventListener("DOMContentLoaded", function (event) {
                    $('.travelshop-datepicker-input').on('click', function () {
                        $('.daterangepicker select').prettyDropdown({
                            height: 30
                        });
                    });
                    $('.monthselect').on('change', function () {
                        $('.daterangepicker select').prettyDropdown({
                            height: 30
                        });
                    });
                });

                // -- show/hide clear button in datepicker
                $('.travelshop-datepicker-input').on('change', function (e) {
                    if ($(e.target).val() != '') {
                        $(e.target).parent().siblings('.datepicker-clear').show();
                    } else {
                        $(e.target).parent().siblings('.datepicker-clear').hide();
                    }
                });

            }
        }

        /**
         * based on this magic date (easter sunday), it's possible to calculate some special dates:
         *
         * offset to german feiertag from the easterdate
         * - Weiberfastnacht	-52
         * - Rosenmontag	-48
         * - Fastnachtsdienstag	-47
         * - Aschermittwoch	-46
         * - Gründonnerstag	-3
         * - Karfreitag	-2
         * - Ostersonntag	0
         * - Ostermontag	+1
         * - Christi Himmelfahrt	+39
         * - Pfingstsonntag	+49
         * - Pfingstmontag	+50
         * - Fronleichnam	+60
         *
         * @param Y Year YYYY
         * @returns dayjs
         */
        this.theEasterDate = function (Y) {
            var C = Math.floor(Y/100);
            var N = Y - 19*Math.floor(Y/19);
            var K = Math.floor((C - 17)/25);
            var I = C - Math.floor(C/4) - Math.floor((C - K)/3) + 19*N + 15;
            I = I - 30*Math.floor((I/30));
            I = I - Math.floor(I/28)*(1 - Math.floor(I/28)*Math.floor(29/(I + 1))*Math.floor((21 - N)/11));
            var J = Y + Math.floor(Y/4) + I + 2 - C + Math.floor(C/4);
            J = J - 7*Math.floor(J/7);
            var L = I - J;
            var M = 3 + Math.floor((L + 40)/44);
            var D = L + 28 - 31*Math.floor(M/4);

            return dayjs().date(D).month(M - 1).year(Y);
        }


        /**
         * Adds the multi item select feature to the the default bootstrap dropdown box
         */
        this.initCategoryTreeSearchBarFields = function(){

            if ($('.dropdown-menu-select').length > 0) {

                // -- prevent dropdown close when clicked inside
                $('.dropdown-menu-select').on('click', function (e) {
                    e.stopPropagation();
                });

                $('.dropdown-menu-select .filter-prompt').on('click', function (e) {
                    e.preventDefault();

                    $(this).parents('.dropdown').find('.dropdown-toggle').trigger('click');

                    e.stopPropagation();
                })

                // -- make dropdown span-checkboxes clickable
                $('.multi-level-checkboxes .form-check span').on('click', function (e) {
                    if ($(e.target).siblings('input').is(':checked')) {
                        $(e.target).siblings('input').prop('checked', false).trigger('change');
                    } else {
                        $(e.target).parent().find('input').prop('checked', true).trigger('change');
                    }
                });


                // -- create label text on input change, put it into span
                $('.dropdown-menu-select').find('input').on('change', function (e) {

                    var placeHolderTag = $(this).parents('.dropdown').find('.selected-options'),
                        placeHolderDefaultText = placeHolderTag.data('placeholder'),
                        placeHolderGetText = placeHolderTag.text(),
                        placeHolderOptionsText = '',
                        that = $(this);

                    if (placeHolderGetText != placeHolderDefaultText) {
                        placeHolderOptionsText = placeHolderGetText;
                    }

                    var thatValue = that.parent().find('> label').text();
                    thatValue = $.trim(thatValue);

                    var allBoxes = $(this).parent().parent().find('input');
                    var allEmpty = true;

                    $(allBoxes).each(function (key, input) {
                        if (input.checked) {
                            allEmpty = false;
                        }
                    });

                    // function to hide/show the clear-button
                    if (!allEmpty) {
                        $(this).parent().parent().parent().parent().find('.dropdownReiseziel .dropdown-clear').show();
                    } else {
                        $(this).parent().parent().parent().parent().find('.dropdownReiseziel .dropdown-clear').hide();
                    }

                    if (that.prop('checked') === true) {
                        if (placeHolderOptionsText != '') {
                            placeHolderOptionsText = placeHolderOptionsText + ', ' + thatValue;
                        } else {
                            placeHolderOptionsText = thatValue;
                        }
                    } else {
                        if (placeHolderGetText.indexOf(',') != -1) {
                            if (placeHolderGetText.indexOf(thatValue) == 0) {
                                placeHolderOptionsText = placeHolderOptionsText.replace(thatValue + ', ', '');
                            } else {
                                placeHolderOptionsText = placeHolderOptionsText.replace(', ' + thatValue, '');
                            }
                        } else {
                            placeHolderOptionsText = $.trim(placeHolderDefaultText);
                        }
                    }

                    placeHolderTag.text(placeHolderOptionsText);

                });

                $('.dropdown-clear').on('click', function (e) {
                    e.stopPropagation();
                    var placeHolderTag = $(e.target).parent().parent().find('.selected-options');
                    var dropdown = $(e.target).parent().parent().parent().find('.dropdown-menu');
                    var allBoxes = $(dropdown).find('input');

                    $(allBoxes).each(function (key, input) {
                        if (input.checked) {
                            $(input).prop('checked', false).trigger('change');
                        }
                    });

                    $(placeHolderTag).empty().text('bitte wählen');

                    $(e.target).hide();
                });

                // Init on load
                $('.dropdown-menu-select input:checked').trigger('change');

            }



        }

        this.initCalendarRowClick = function(){
            if ( $('.product-calendar-group--items').length > 0 ) {
                $('.product-calendar-group--items').on('click', '.product-calendar-group-item', function(e) {
                    e.preventDefault();
                    let row_id = $(this).data('row-id');

                    if ( $(this).hasClass('is--active') ) { // close
                        $(this).removeClass('is--active');
                        $('.product-calendar-group-item--product[data-row-id="'+row_id+'"]').removeClass('is--open');

                    } else { // open & load product
                        let pm_id = $(this).data('pm-id');
                        let pm_dr = $(this).data('pm-dr');
                        $('.product-calendar-group-item').removeClass('is--active');
                        $('.product-calendar-group-item--product').removeClass('is--open');
                        $(this).addClass('is--active');
                        let query_string = 'action=pm-view&view=Teaser3&pm-id='+pm_id+'&pm-dr='+pm_dr;
                        _this.call(query_string, null, null, _this.calendarRowClickResultHandler, '.product-calendar-group-item--product[data-row-id="'+row_id+'"]');
                        $('.product-calendar-group-item--product[data-row-id="'+row_id+'"]').addClass('is--open');
                    }
                    e.stopPropagation();
                })
            }
        }

        this.calendarRowClickResultHandler = function (data, query_string, scrollto, total_result_span_id, target){
            $(target).html(data.html);
            _this.wishlistEventListeners();
            _this.wishListInit();
        }

        this.checkAvailability = function (id_offer, quantity, booking_btn){
            this.requests.push($.ajax({
                url: ts_ajax_check_availibility_endpoint,
                type: 'POST',
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify({'checks' : [{
                    'id_offer' : id_offer,
                    'quantity' : quantity
                }]}),
            }).done(function (response) {
                $(booking_btn).find('span').html(response.data[0].btn_msg);
                $(booking_btn).attr('title', response.data[0].msg);
                $(booking_btn).find('.loader').hide();
                $(booking_btn).removeClass('green');
                $(booking_btn).addClass(response.data[0].class);
                if(response.data[0].bookable === true){
                    $(booking_btn).find('svg').show();
                    location.href = $(booking_btn).attr('href') + '&t='+response.data[0].booking_type;
                }
            }));
        }

        this.initBookingBtnClickHandler = function (){
            if ($('.booking-btn').length > 0) {
                $('.booking-btn').on('click', function (e) {
                    if($(this).data('modal') === true){
                        return true;
                    }
                    e.stopPropagation();
                    e.preventDefault();
                    $(this).find('.loader').show();
                    $(this).find('svg').hide();
                    $(this).find('span').html('');
                    _this.checkAvailability($(this).data('id-offer'), 1, this);
                });
            }
        }

        this.init = function(){
            _this.renderWishlist();
            _this.wishlistEventListeners();
            _this.wishListInit();
            _this.pagination();
            _this.searchbox();
            _this.searchboxSwitch();
            _this.filter();
            _this.autoCompleteInit();
            _this.dateRangePickerInit();
            _this.initCategoryTreeSearchBarFields();
            _this.initCalendarRowClick();
            _this.initBookingBtnClickHandler();
        }

    };

    var Search = new TSAjax('/wp-content/themes/travelshop/pm-ajax-endpoint.php');
    Search.init();
});