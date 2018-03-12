<?php
namespace TukosLib\Objects\Wine;

class Wine {
    public static $statusOptions = ['IGNORE'/* ignore in cellar update process*/, 'PENDING'/* Not yet entered into cellar*/, 'PROCESSED'/*Cellar has been updated*/];
    public static $whereObtainedOptions = ['ND'/* not defined*/, 'FR'/*Dealer Fair*/, 'SL'/* Wine Fair*/, 'PR'/*Producer*/, 'DL'/*dealer*/, 'GT'/*gift*/];
    public static $formatOptions = ['BT'/* Bottle*/, 'MG'/*Magnum*/, 'DM'/*Double Magnum*/, 'JB'/* Jeroboam*/, 'MT'/*Mathusalem*/, 'BB'/*bib*/, 'CU'/*cubitainer*/];
    public static $laydownOptions = ['Immediate', 'Short', 'Medium', 'Long', 'Very Long'];
    public static $gradingOptions = ['VDF'/*Vin de France*/, 'IGP'/*Indication géographique Controlée*/, 'AOC'/*Appellation d'Origine Contrôlée*/, 'CB'/*Cru Bourgeois*/];
    public static $grapeOptions = [
        'ND'/* not defined*/, 'AX'/*Auxerrois*/, 'CB'/*Cabernet*/, 'CF'/*Cabernet Franc*/, 'CL'/*Chasselas*/, 'CY'/*Chardonnay*/, 'CN'/*Chenin*/, 'GW'/*Gewurtzstraminer*/, 
        'MLB'/*Melon de Bourgogne*/, 'MSC'/*Muscat*/, 'PN'/*Pinot noir*/, 'PG'/*Pinot gris*/, 'PB'/*Pinot blanc*/,'RG'/*Riesling*/, 'SN'/*Sauvignon*/, 'MX'/* Mixture*/
    ];
    public static $categoryOptions = ['TQ'/* Tranquille*/, 'EF'/*Effervescent*/, 'VD'/* Vin Doux Naturel*/, 'VL'/*Vin de liqueur*/];
    public static $colorOptions = ['RD'/* red*/, 'WH'/*white*/, 'RS'/* rosé*/];
    public static $sugarOptions = ['ND'/*not defined*/, 'LX'/*Liquoreux*/, 'ML'/*Moelleux*/, 'DX'/*Doux*/, 'SC'/* Sec*/, 'DS'/*Demi sec*/, 'BR'/* Brut*/];
}
?>
