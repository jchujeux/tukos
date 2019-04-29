<?php
namespace TukosLib\Objects\Itm;

class Itm {

    public static $ItsmProcessOptions = ['incident', 'svcrequest', 'problem'];
    public static $indicatorOptions   = ['priority'];
    public static $notifiedViaOptions = ['telephone', 'email', 'tukos', 'monitoring', 'other'];
    public static $callbackOptions    = ['telephone', 'email', 'tukos', 'other'];
    public static $urgencyOptions     = ['Low', 'Medium', 'High'];
    public static $impactOptions      = ['Low', 'Medium', 'High'];
    public static $priorityOptions    = ['verylow', 'low', 'medium', 'high', 'critical'];
    //public static $majorFlagOptions   = ['yes', 'no'];
    public static $categoryOptions    = [
        'app-svc-not-avail', 'app-data-issue', 'app-bug', 'app-perf', 'app-other', 'hard-server-down', 'hard-net-issue', 'hard-auto-alert', 'hard-printer-issue', 'hard-other', 'other'
    ];
    public static $ciTypeOptions      = ['system', 'hardware', 'application', 'documentation'];
    public static $ciStatusOptions    = ['development', 'test', 'production', 'maintenance', 'retired'];
    public static $trustOptions       = ['TRUSTED'/* */, 'UNKNOWN'/* */, 'SUSPECT'/* */, 'MALICIOUS'/* */,];
    public static $osFamilyOptions    = ['Windows'/* */, 'Linux'/* */, 'FreeBSD'/* */, 'MacOS'/* */, 'IOS'/* */, 'Android'/* */, 'Other'/* */,];
    public static $hostTypeOptions    = ['Smartphone'/* */, 'Tablet'/* */, 'Notebook'/* */, 'Desktop'/* */, 'Server'/* */, 'Printer'/* */, 'Network'/* */, 'Other'/* */,];
    public static $incidentsProgressOptions = ['submitted', 'logging and categorization', 'resolution', 'on hold', 'resolved', 'closed'];
}
?>
