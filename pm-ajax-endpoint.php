<?php
/**
 * Custom Ajax Endpoint to avoid loading a full wordpress stack load with WordPress's regular admin-ajax.php
 */

use \Pressmind\Travelshop\Search;
use \Pressmind\ORM\Object\MediaObject;
use \Pressmind\Search\CheapestPrice;
use \Pressmind\Travelshop\PriceHandler;
use \Pressmind\Travelshop\IB3Tools;

//error_reporting(-1);
//ini_set('display_errors', 'On');

require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();
require_once getenv('CONFIG_THEME');
require_once 'bootstrap.php';
require_once 'src/Search.php';
require_once 'src/BuildSearch.php';
require_once 'src/RouteHelper.php';
require_once 'src/PriceHandler.php';
require_once 'src/Template.php';
require_once 'src/IB3Tools.php';
header('Content-type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
$Output = new stdClass();
$Output->error = true;
$Output->result = null;
$Output->html = array();
$Output->msg = null;
$Output->count = null;
$request = json_decode(file_get_contents('php://input'));
if (empty($_GET['action'])) {
    $Output->result = $request;
    echo json_encode($Output);
    exit;
} else if ($_GET['action'] == 'search') {
    $args = Search::getResult($_GET, 2, 12, true, false);
    ob_start();
    require 'template-parts/pm-search/result.php';
    $Output->count = (int)$args['total_result'];
    $Output->html['search-result'] = ob_get_contents();
    ob_end_clean();
    ob_start();
    require 'template-parts/pm-search/filter-vertical.php';
    $Output->html['search-filter'] = ob_get_contents();
    ob_end_clean();
    $Output->error = false;
    $result = json_encode($Output);
    echo $result;
    exit;
} else if ($_GET['action'] == 'wishlist'){
    /**
     * @var array $result
     * @var array $ids
     */
    ob_start();
    require 'template-parts/pm-search/wishlist-result.php';
    $Output->count = $result['total_result'];
    $Output->mongo = $result['mongodb'];
    $Output->ids = $ids;
    $Output->html['wishlist-result'] = ob_get_contents();
    ob_end_clean();
    $Output->error = false;
    $result = json_encode($Output);
    echo $result;
    exit;
} else if ($_GET['action'] == 'searchbar'){
    $args = Search::getResult($_GET, 2, 12, true, false);
    ob_start();
    require 'template-parts/pm-search/search/searchbar-form.php';
    $Output->html['main-search'] = ob_get_contents();
    ob_end_clean();
    $Output->error = false;
    $result = json_encode($Output);
    echo $result;
    exit;
} else if ($_GET['action'] == 'autocomplete') {
    ob_start();
    require 'template-parts/pm-search/autocomplete.php';
    $output = ob_get_contents();
    ob_end_clean();
    echo $output;
    exit;
} else if ($_GET['action'] == 'pm-view') {
    $id_media_object = (int)$_GET['pm-id'];
    if(empty($id_media_object)){
        exit;
    }
    $view = 'Teaser1';
    if(!empty($_GET['view']) && preg_match('/^[0-9A-Za-z\_]+$/', $_GET['view']) !== false){
        $view = $_GET['view'];
    }
    $result = Search::getResult(['pm-id' => $id_media_object], 2, 1, false, false);
    $Output->error = true;
    $Output->html = '<!-- media object not found -->';
    if(!empty($result['items'][0])){
        $Output->error = false;
        $Output->html = \Pressmind\Travelshop\Template::render(__DIR__ . '/template-parts/pm-views/' . $view . '.php', $result['items'][0]);
    }
    $result = json_encode($Output);
    echo $result;
    exit;
}else if ($_GET['action'] == 'offers') {
    $id_media_object = (int)$_GET['pm-id'];
    if(empty($id_media_object)){
        exit;
    }
    $mediaObject = new MediaObject($id_media_object);
    $filter = new CheapestPrice();
    if (isset($_GET['pm-du']) === true && preg_match('/^([0-9]+)\-([0-9]+)$/', $_GET['pm-du']) > 0) {
        list($filter->duration_from, $filter->duration_to) = explode('-', $_GET['pm-du']);
    }
    if (isset($_GET['pm-dr']) === true) {
        $dateRange = BuildSearch::extractDaterange($_GET['pm-dr']);
        if($dateRange !== false){
            $filter->date_from = $dateRange[0];
            $filter->date_to = $dateRange[1];
        }
    }
    $limit = [0,100];
    if (isset($_GET['pm-l']) === true && preg_match('/^([0-9]+)\,([0-9]+)$/', $_GET['pm-l'], $m) > 0) {
        $limit = [intval($m[1]), intval($m[2])];
    }
    $filter->occupancies_disable_fallback = true;
    $prices = $mediaObject->getCheapestPrices($filter, ['date_departure' => 'ASC', 'price_total' => 'ASC'], $limit);
    $offers = [];
    foreach($prices as $price){
        $tmp = new \stdClass();
        $tmp = $price->toStdClass(false);
        $tmp->price_total_formatted = PriceHandler::format($price->price_total);
        $tmp->ib3_url = IB3Tools::get_bookinglink($id_media_object, $price->id_booking_package, $price->id_date, $price->id_housing_package);
        $offers[] = $tmp;
    }
    $result = json_encode($offers);
    echo $result;
    exit;
}else{
    header("HTTP/1.0 400 Bad Request");
    $Output->msg = 'error: action not known';
    $Output->error = true;
    echo json_encode($Output);
    exit;
}
