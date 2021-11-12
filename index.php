<?php

use Kirby\Cms\Response;

require __DIR__.DS."src".DS."Autopublish.php";

// For composer
@include_once __DIR__.'/vendor/autoload.php';

Kirby::plugin('bvdputte/kirbyAutopublish', [
    'options' => [
        'fieldName' => 'autopublish',
        'fieldNameToggle' => 'autopublishtoggle',
        'fieldNameUnpublish' => 'autounpublish',
        'fieldNameUnpublishToggle' => 'autounpublishtoggle',
        'poormanscron' => false,
        'poormanscron.interval' => 1, // in minutes
        'cache.poormanscron' => true,
        'webhookToken' => false,
    ],
    'collections' => [
        'autoPublishedDrafts' => function ($site) {
            $autopublishfield = option("bvdputte.kirbyAutopublish.fieldName");
            $autopublishfieldtoggle = option("bvdputte.kirbyAutopublish.fieldNameToggle");
            $drafts = $site->index()->drafts();

            return $drafts->filter(function ($draft) use ($autopublishfield, $autopublishfieldtoggle) {
                $hasToggle = $draft->$autopublishfieldtoggle()->exists();
                $isEnabled = $draft->$autopublishfieldtoggle()->value();
                $autopublish = $draft->$autopublishfield()->exists() && $draft->$autopublishfield()->isNotEmpty() && empty($draft->errors());

                if (!$hasToggle) {
                    return $autopublish;
                }

                if ($isEnabled) {
                    return $autopublish;
                }

                return false;
            });
        },
        'autoUnpublishedListed' => function ($site) {
            $autounpublishfield = option("bvdputte.kirbyAutopublish.fieldNameUnpublish");
            $autounpublishfieldtoggle = option("bvdputte.kirbyAutopublish.fieldNameUnpublishToggle");
            $listed = $site->index()->children()->listed();

            return $listed->filter(function ($listedPage) use ($autounpublishfield, $autounpublishfieldtoggle) {
                $hasToggle = $listedPage->$autounpublishfieldtoggle()->exists();
                $isEnabled = $listedPage->$autounpublishfieldtoggle()->value();
                $autounpublish = $listedPage->$autounpublishfield()->exists() && $listedPage->$autounpublishfield()->isNotEmpty() && empty($listedPage->errors());

                if (!$hasToggle) {
                    return $autounpublish;
                }

                if ($isEnabled) {
                    return $autounpublish;
                }

                return false;
            });
        }
    ],
    'hooks' => [
        'route:before' => function ($route, $path, $method) {
            /*
             * For servers without cron, enable "poormanscron"
             * ⚠️ Ugly, non-performant hack to bypass cache
             */
            if (option("bvdputte.kirbyAutopublish.poormanscron")) {
                bvdputte\kirbyAutopublish\Autopublish::poorManCronRun();
            }
        }
    ],
    'routes' => [
        [
            'pattern' => 'kirby-autopublish/(:any)',
            'action' => function ($token) {
                if (
                    $token !== option('bvdputte.kirbyAutopublish.webhookToken', false) ||
                    option('bvdputte.kirbyAutopublish.webhookToken', false) === false
                ) {
                    throw new Exception('Invalid token');
                }

                bvdputte\kirbyAutopublish\Autopublish::publish();
                bvdputte\kirbyAutopublish\Autopublish::unpublish();

                return new Response('done', 'text/html');
            }
        ],
    ]
]);
