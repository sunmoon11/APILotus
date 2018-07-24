<?php
require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';
require_once '../../lib/utils/TimeUtils.php';

$dbApi = DBApi::getInstance();

$sub_domains = $dbApi->getAllSubDomain();
foreach ($sub_domains as $item)
{
    $name = $item[1];
    $features = $dbApi->getFeatureEnableList($name);
    $features = explode(',', $features);
    if (!in_array(3, $features)) // if affiliate disabled in admin panel
        continue;
    getAffiliateCache($name);
    break;
}
return;

function getAffiliateCache($sub_domain)
{

    $timeUtil = TimeUtils::getInstance();
    $fromDate = $timeUtil->getDateOfYesterday();
    $toDate = $timeUtil->getDateOfYesterday();
    $from = date('Y-m-d', strtotime($fromDate));
    $to = date('Y-m-d', strtotime($toDate));

    $type = 1;
    global $dbApi;
    $dbApi->setSubDomain($sub_domain);

    $crmList = $dbApi->getAllActiveCrm();
    if ($crmList === array())
    {
        echo "There is no active crm in this sub domain\n";
        return;
    }
    $timestamp = date('Y-m-d H:i:s');
    echo "start time: $timestamp \n";

    $affiliateData = array();
    foreach ($crmList as $crmInfo)
    {
        $crmID = $crmInfo[0];
        $crmUrl = $crmInfo[2];
        $userName = $crmInfo[3];
        $password = $crmInfo[4];

        $llcrmHook = new LLCrmHook();
        if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
        {
            // get prospect page
            $prospectPage = $llcrmHook->getProspectReport($token, $fromDate, $toDate);
            $initialCustomers = $llcrmHook->parse4InitialCustomerByCampaign($prospectPage);

            $affiliate_crm = array();
            if ($initialCustomers != array())
            {
                // delete old cached yesterday`s data
                $dbApi->deleteAffiliateCacheByCrmID($crmID, $type);

                // get affiliate prospect page

                $affLabelingTable = $dbApi->getAffiliatesByCrmId($crmID);
                foreach ($initialCustomers as $item)
                {
                    $campaignID = $item['campaign_id'];
                    $affiliates = $llcrmHook->getAffiliateReportByCampaign($token, $fromDate, $toDate, $campaignID);

                    $data = array();
                    foreach ($affiliates as $value)
                    {
                        // get a labeling for affiliate
                        $aff_label = '';
                        foreach ($affLabelingTable as $affLabel)
                        {
                            if ($affLabel[0] == $value['affiliate_id'])
                            {
                                $aff_label = $affLabel[1];
                                break;
                            }
                        }
                        if($aff_label != '')
                        {
                            $affiliate_campaign['crm_id'] = $crmID;
                            $affiliate_campaign['campaign_id'] = $campaignID;
                            $affiliate_campaign['affiliate_id'] = $value['affiliate_id'];
                            $affiliate_campaign['initial_customer'] = $item['initial_customer'];
                            $affiliate_campaign['label'] = $aff_label;
                            $data[] = $affiliate_campaign;
                        }
                    }
                    // cache affiliate report by camapaign and search type
                    $affiliate_crm[] = $data;
                }
                $affiliateData[] = $affiliate_crm;
            }
            else
            {
                echo $crmID.": Prospect is empty\n";
            }
        }
        else
        {
            echo "Crm login failed\n";
        }

    }

    $to = date('Y-m-d H:i:s');
    foreach ($affiliateData as $affiliate_crm)
    {
        foreach ($affiliate_crm as $affiliate_campaign)
            $dbApi->addAffiliateCache($affiliate_campaign, $timestamp, $to, $type);
    }
    $timestamp = date('Y-m-d H:i:s');
    echo "end time: $timestamp \n";
}