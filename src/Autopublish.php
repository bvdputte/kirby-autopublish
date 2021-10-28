<?php

namespace bvdputte\kirbyAutopublish;

class Autopublish {

    public static function publish()
    {
        $kirby = kirby();
        $autopublishfield = option("bvdputte.kirbyAutopublish.fieldName");
        $pagesToPublish = $kirby->collection("autoPublishedDrafts")
                                ->filter(function ($draft) use ($autopublishfield) {
            $publishTime = new \Datetime($draft->$autopublishfield());
            return $publishTime->getTimestamp() < time();
        });

        // Publish pages which are due
        kirby()->impersonate("kirby");
        foreach($pagesToPublish as $p) {
            try {
                $p->changeStatus("listed");
                self::log("Autopublished " . $p->id());
            } catch (Exception $e) {
                self::log("Error autopublishing " .  $p->id(), "error", [$e->getMessage()]);
            }
        }
    }

    public static function unpublish()
    {
        $kirby = kirby();
        $autounpublishfield = option("bvdputte.kirbyAutopublish.fieldNameUnpublish");
        $pagesToUnPublish = $kirby->collection("autoUnpublishedListed")
                                ->filter(function ($p) use ($autounpublishfield) {
            $unpublishTime = new \Datetime($p->$autounpublishfield());
            return $unpublishTime->getTimestamp() < time();
        });

        // Unpublish pages which are due
        kirby()->impersonate("kirby");
        foreach($pagesToUnPublish as $p) {
            try {
                $p->changeStatus("draft");
                self::log("Auto-unpublished " . $p->id());
            } catch (Exception $e) {
                self::log("Error auto-unpublishing " . $p->id(), "error", [$e->getMessage()]);
            }
        }
    }

    public static function poorManCronRun()
    {
        $pmcCache = kirby()->cache("bvdputte.kirbyAutopublish.poormanscron");
        $lastRun = $pmcCache->get("lastrun");

        if ($lastRun === null) {
            self::publish();
            self::unpublish();
            $expire = option("bvdputte.kirbyAutopublish.poormanscron.interval");
            $pmcCache->set("lastrun", time(), $expire);
        }
    }

    private static function log($message, $lvl = null, $context = [])
    {
        if(is_a(site()->logger(), 'bvdputte\kirbyLog\Logger')) {
            site()->logger("autopublish.log")->log($message, $lvl, $context);
        } elseif($lvl == 'error') {
            error_log($message);
        }
    }
}
