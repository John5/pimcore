<?php
/**
 * Pimcore
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @category   Pimcore
 * @package    Document
 * @copyright  Copyright (c) 2009-2010 elements.at New Media Solutions GmbH (http://www.elements.at)
 * @license    http://www.pimcore.org/license     New BSD License
 */

class Tool_ContentAnalysis_Service_Resource extends Pimcore_Model_Resource_Abstract {

    public function cleanupExistingData($patterns) {

        foreach ($patterns as $pattern) {

            // remove delimiters
            $delemiter = substr($pattern, 0, 1);
            if($last = strrpos($pattern, $delemiter, 1)) {
                $pattern = substr_replace($pattern, "", 0, 1);
                $pattern = substr_replace($pattern, "", ($last-1));
            }

            try {
                $this->db->delete("content_analysis", "url REGEXP(" . $this->db->quote($pattern) . ")");
            } catch (Exception $e) {

            }

            try {
                $this->db->delete("content_index", "url REGEXP(" . $this->db->quote($pattern) . ")");
            } catch (Exception $e) {

            }
        }

    }

    public function getOverviewData ($site = null) {

        $summary = array();

        $siteCondition = "1=1";
        if($site == "default") {
            $siteCondition = "(site IS NULL OR site = '')";
        } else if ($site != null) {
            $siteCondition = "(site = '" . $site . "')";
        }

        $robotsCondition = "robotsTxtBlocked = 0 AND robotsMetaBlocked = 0";

        $summary["title_dublicate"] = (int) $this->db->fetchOne("SELECT SUM(amount) FROM (SELECT COUNT(*) AS amount FROM content_analysis WHERE LENGTH(title) > 0 AND " . $robotsCondition . " AND " . $siteCondition . " GROUP BY title HAVING amount > 1) dummy_alias");
        $summary["title_empty"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE LENGTH(title) < 1 AND " . $robotsCondition . " AND " . $siteCondition . "");
        $summary["title_tooShort"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE LENGTH(title) < 8 AND LENGTH(title) > 0 AND " . $robotsCondition . " AND " . $siteCondition . "");
        $summary["title_tooLong"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE LENGTH(title) > 70 AND " . $robotsCondition . " AND " . $siteCondition . "");

        $summary["description_dublicate"] = (int) $this->db->fetchOne("SELECT SUM(amount) FROM (SELECT COUNT(*) AS amount FROM content_analysis WHERE LENGTH(description) > 0 AND " . $robotsCondition . " AND " . $siteCondition . " GROUP BY description HAVING amount > 1) dummy_alias");
        $summary["description_empty"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE LENGTH(description) < 1 AND " . $robotsCondition . " AND " . $siteCondition . "");

        $summary["headline_h1Missing"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE LENGTH(h1Text) < 1 AND " . $robotsCondition . " AND " . $siteCondition . "");

        $summary["url_tooLong"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE urlLength > 115 AND " . $robotsCondition . " AND " . $siteCondition . "");
        $summary["url_tooMuchParameters"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE urlParameters > 2 AND " . $robotsCondition . " AND " . $siteCondition . "");

        $summary["blocked_meta"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE robotsMetaBlocked > 0 AND " . $siteCondition . "");
        $summary["blocked_txt"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE robotsTxtBlocked > 0 AND " . $siteCondition . "");

        $summary["image_withoutAlt"] = (int) $this->db->fetchOne("SELECT SUM(imgWithoutAlt) FROM content_analysis WHERE imgWithoutAlt > 1 AND " . $robotsCondition . " AND " . $siteCondition . "");

        $summary["social_facebookShares"] = (int) $this->db->fetchOne("SELECT SUM(facebookShares) FROM content_analysis WHERE " . $siteCondition . "");
        $summary["social_googlePlusOne"] = (int) $this->db->fetchOne("SELECT SUM(googlePlusOne) FROM content_analysis WHERE " . $siteCondition . "");

        $summary["meta_microdata"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE microdata > 0 AND " . $robotsCondition . " AND " . $siteCondition . "");
        $summary["meta_opengraph"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE opengraph > 0 AND " . $robotsCondition . " AND " . $siteCondition . "");
        $summary["meta_twitter"] = (int) $this->db->fetchOne("SELECT COUNT(*) FROM content_analysis WHERE twitter > 0 AND " . $robotsCondition . " AND " . $siteCondition . "");

        return $summary;
    }
}
