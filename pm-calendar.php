<?php
/**
 * @var WP_Query $wp_query
 */
use \Pressmind\Travelshop\Search;
get_header();
?>
    <main>
        <?php
            $output = 'date_list';
            $request = [];
            $request = array_merge($_GET, ['pm-ot' => $wp_query->get('pm-ot')]);
            $request['pm-o'] = 'date_departure-asc';
            $_GET['view'] = 'Calendar1';
            $result = Search::getResult($request,2, 12, true, false, TS_TTL_FILTER, TS_TTL_SEARCH, $output);
            $result['calendarpage'] = true;
            ?>
        <div class="content-main">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h1 style="margin: 1.5rem 0 .5rem 0">Reisekalender</h1>
                        <div id="search-result">
                            <?php
                            // this content will be replaced by ajax during the search, @see travelshop/assets/js/ajax.js
                            load_template_transient(get_stylesheet_directory() . '/template-parts/pm-search/result.php', false, $result);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php
get_footer();