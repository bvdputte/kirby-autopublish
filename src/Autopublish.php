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
                if(function_exists('kirbyLog')) {
                    kirbyLog("autopublish.log")->log("Autopublished " . $p->id(), "info");
                }
            } catch (Exception $e) {
                if(function_exists('kirbyLog')) {
                    kirbyLog("autopublish.log")->log("Error adding " .  $newPage->id() . " to autopublish queue", "error", [$e->getMessage()]);
                } else {
                    error_log("Error adding " .  $newPage->id() . " to autopublish queue");
                }
            }
        }
    }

    public static function poorManCronRun()
    {
        $pmcCache = kirby()->cache("bvdputte.kirbyAutopublish.poormanscron");
        $lastRun = $pmcCache->get("lastrun");

        if ($lastRun === null) {
            self::publish();
            $expire = option("bvdputte.kirbyAutopublish.poormanscron.interval");
            $pmcCache->set("lastrun", time(), $expire);
        }
    }

}