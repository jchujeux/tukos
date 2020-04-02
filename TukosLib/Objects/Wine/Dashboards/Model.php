<?php
/**
 *
 * class for the tukos wine application dashboard object, i.e; the KPIs of the Wine Cellar
 */
namespace TukosLib\Objects\Wine\Dashboards;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk; 

class Model extends AbstractModel { 
    function __construct($objectName, $translator=null){
        $colsDefinition = [ 'count'                         => 'INT(11) NOT NULL',
                            'quantity'                      => 'INT(11) NOT NULL',
                            'countperregion'                =>  'longtext',
                            'quantityperregion'             =>  'longtext',
                            'countpercategory'              =>  'longtext',
                            'quantitypercategory'           =>  'longtext',
                            'countpercolor'                 =>  'longtext',
                            'quantitypercolor'              =>  'longtext',
                            'countpersugar'                 =>  'longtext',
                            'quantitypersugar'              =>  'longtext',
                            'countpervintage'               =>  'longtext',
                            'quantitypervintage'            =>  'longtext',
                            'countpercategoryperregion'     =>  'longtext',
                            'quantitypercategoryperregion'  =>  'longtext',
                            'countpercolorperregion'        =>  'longtext',
                            'quantitypercolorperregion'     =>  'longtext',
                            'countpersugarperregion'        =>  'longtext',
                            'quantitypersugarperregion'     =>  'longtext',
                            'countpervintageperregion'      =>  'longtext',
                            'quantitypervintageperregion'   =>  'longtext',
                            'inventorydate'                 =>  "date",
                          ];
        parent::__construct($objectName, $translator, 'winedashboards', ['parentid' => ['winecellars']], [], $colsDefinition,  [], [], ['custom']);
        $this->wTr = (new ObjectTranslator('wines'))->tr;
    }
    public function currentKPIs($cellarid = null){
        $tk = SUtl::$tukosTableName;
        $results = SUtl::$tukosModel->store->query(
            'SELECT count(*), sum(quantity), t3.name, wines.category, wines.color, wines.sugar, winestock.vintage ' .
            'FROM winestock ' .
                'INNER JOIN (' . $tk . ' as t0 INNER JOIN wines) ON (t0.id = winestock.id AND t0.parentid = wines.id) ' .
                'INNER JOIN (' . $tk . ' as t1 INNER JOIN wineappellations) ON (t1.id = wines.id AND t1.parentid = wineappellations.id) ' .
                'INNER JOIN (' . $tk . ' as t2 INNER JOIN wineregions) ON (t2.id = wineappellations.id AND t2.parentid = wineregions.id) ' .
                'INNER JOIN ' . $tk . ' as t3  ON t3.id = wineregions.id ' .
            'WHERE winestock.quantity > 0 AND winestock.cellarid = ' . $cellarid . ' ' .
            'GROUP BY t3.name, wines.category, wines.color, wines.sugar, winestock.vintage'
        );

        $kpis['count'] = 0;
        $kpis['quantity'] = 0;

        $kpis['countperregion']         = [];
        $kpis['quantityperregion']      = [];
        $kpis['countpercategory']       = [];
        $kpis['quantitypercategory']    = [];
        $kpis['countpercolor']          = [];
        $kpis['quantitypercolor']       = [];
        $kpis['countpersugar']          = [];
        $kpis['quantitypersugar']       = [];
        $kpis['countpervintage']        = [];
        $kpis['quantitypervintage']     = [];

        $kpis['countpercategoryperregion']     = [];
        $kpis['quantitypercategoryperregion']  = [];
        $kpis['countpercolorperregion']        = [];
        $kpis['quantitypercolorperregion']     = [];
        $kpis['countpersugarperregion']        = [];
        $kpis['quantitypersugarperregion']     = [];
        $kpis['countpervintageperregion']      = [];
        $kpis['quantitypervintageperregion']   = [];

        $region = 'name';
        foreach ($results as $i =>$result){

            $kpis['count']    += $result['count(*)'];
            $kpis['quantity'] += $result['sum(quantity)'];
            
            Utl::increment($kpis['countperregion']   , $result[$region], $result['count(*)']);
            Utl::increment($kpis['countperregion']   , $result[$region], $result['count(*)']);
            Utl::increment($kpis['quantityperregion'], $result[$region], $result['sum(quantity)']);
            Utl::increment($kpis['countpercategory']   , $result['category'], $result['count(*)']);
            Utl::increment($kpis['quantitypercategory'], $result['category'], $result['sum(quantity)']);
            Utl::increment($kpis['countpercolor']      , $result['color'], $result['count(*)']);
            Utl::increment($kpis['quantitypercolor']   , $result['color'], $result['sum(quantity)']);
            Utl::increment($kpis['countpersugar']      , $result['sugar'], $result['count(*)']);
            Utl::increment($kpis['quantitypersugar']   , $result['sugar'], $result['sum(quantity)']);
            Utl::increment($kpis['countpervintage']    , $result['vintage'], $result['count(*)']);
            Utl::increment($kpis['quantitypervintage'] , $result['vintage'], $result['sum(quantity)']);

            Utl::increment2D($kpis['countpercategoryperregion']   , $result[$region], $result['category'], $result['count(*)']);
            Utl::increment2D($kpis['quantitypercategoryperregion'], $result[$region], $result['category'], $result['sum(quantity)']);
            Utl::increment2D($kpis['countpercolorperregion']      , $result[$region], $result['color'], $result['count(*)']);
            Utl::increment2D($kpis['quantitypercolorperregion']   , $result[$region], $result['color'], $result['sum(quantity)']);
            Utl::increment2D($kpis['countpersugarperregion']      , $result[$region], $result['sugar'], $result['count(*)']);
            Utl::increment2D($kpis['quantitypersugarperregion']   , $result[$region], $result['sugar'], $result['sum(quantity)']);
            Utl::increment2D($kpis['countpervintageperregion']    , $result[$region], $result['vintage'], $result['count(*)']);
            Utl::increment2D($kpis['quantitypervintageperregion'] , $result[$region], $result['vintage'], $result['sum(quantity)']);
        }
        return $kpis;
    }
    function processOne($where){
        $where = $this->user->filter($where, $this->objectName);
        $values = $this->getOne(['where' => $where, 'cols' => ['parentid']]);
        if (isset($values['parentid'])){
            $kpiValues = $this->currentKPIs($values['parentid']);
            $kpiValues['quantityperregion']     = Utl::toStoreData($kpiValues['quantityperregion'], 'region', 'qty');
            $kpiValues['countperregion']        = Utl::toStoreData($kpiValues['countperregion'], 'region', 'count');
            $kpiValues['quantitypercategory']   = Utl::toStoreData($kpiValues['quantitypercategory'], 'category', 'qty');
            $kpiValues['countpercategory']      = Utl::toStoreData($kpiValues['countpercategory'], 'category', 'count');
            $kpiValues['quantitypercolor']      = Utl::toStoreData($kpiValues['quantitypercolor'], 'color', 'qty');
            $kpiValues['countpercolor']         = Utl::toStoreData($kpiValues['countpercolor'], 'color', 'count');
            $kpiValues['quantitypersugar']      = Utl::toStoreData($kpiValues['quantitypersugar'], 'sugar', 'qty');
            $kpiValues['countpersugar']         = Utl::toStoreData($kpiValues['countpersugar'], 'sugar', 'count');
            $kpiValues['quantitypervintage']    = Utl::toStoreData($kpiValues['quantitypervintage'], 'vintage', 'qty');
            $kpiValues['countpervintage']       = Utl::toStoreData($kpiValues['countpervintage'], 'vintage', 'count');

            $kpiValues = Utl::jsonEncodeArray($kpiValues);
            $this->updateOne($kpiValues, ['where' => $where]);
            Feedback::add($this->tr('Dashboard updated'));
        }else{
            Feedback::add('Cellar Id not set');
        }
        return [];
    }
    public function getOneExtended ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $wTr = $this->wTr;
        $result = parent::getOneExtended($atts);
        $kpiAttTypes = ['region', 'category', 'color', 'sugar', 'vintage'];
        $kpiValTypes = ['quantity', 'count'];
        foreach ($kpiAttTypes as $attType){
            foreach ($kpiValTypes as $valType){
                $kpiCol = $valType . 'per' . $attType;
                if (!empty($result[$kpiCol])){
                    $values = json_decode($result[$kpiCol], true);
                    foreach ($values as &$row){
                        forEach ($row as $att => $value){
                    		$trAtt = $att;//$wTr($att);
                    		$row[$trAtt] = $wTr(ucfirst(mb_strtolower(Utl::extractItem($att, $row))));
                        }
                    }
                    $result[$kpiCol] = ['store' => $values];
                }
            }
        }
        return $result;
    }
}
?>
