<?php
/**
 * Freeform for Craft CMS.
 *
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2022, Solspace, Inc.
 *
 * @see           https://docs.solspace.com/craft/freeform
 *
 * @license       https://docs.solspace.com/license-agreement
 */

namespace Solspace\Freeform\Controllers;

use Solspace\Freeform\Freeform;
use Solspace\Freeform\Resources\Bundles\DiagnosticsBundle;
use yii\web\Response;

class DiagnosticsController extends BaseController
{
    public function actionIndex()
    {
        \Craft::$app->view->registerAssetBundle(DiagnosticsBundle::class);

        $diagnostics = Freeform::getInstance()->diagnostics;

        $server = $diagnostics->getServerChecks();
        $stats = $diagnostics->getFreeformStats();
        $checks = $diagnostics->getFreeformChecks();

        $combined = array_merge($server, $stats, $checks);
        list($warnings, $suggestions) = $this->compileBanners($combined);

        return $this->renderTemplate(
            'freeform/settings/_diagnostics',
            [
                'server' => $server,
                'stats' => $stats,
                'checks' => $checks,
                'warnings' => $warnings,
                'suggestions' => $suggestions,
            ]
        );
    }

    public function actionCraftPreflight(): Response
    {
        \Craft::$app->view->registerAssetBundle(DiagnosticsBundle::class);

        $preflight = Freeform::getInstance()->preflight;

        list($warnings, $suggestions) = $this->compileBanners($preflight->getItems());

        return $this->renderTemplate(
            'freeform/settings/_craft-preflight',
            [
                'warnings' => $warnings,
                'suggestions' => $suggestions,
            ]
        );
    }

    private function compileBanners($items): array
    {
        $warnings = $suggestions = [];
        foreach ($items as $item) {
            if (\is_array($item)) {
                list($subWarnings, $subSuggestions) = $this->compileBanners($item);
                $warnings = array_merge($warnings, $subWarnings);
                $suggestions = array_merge($suggestions, $subSuggestions);

                continue;
            }

            if ($item->getWarnings()) {
                $warnings = array_merge($warnings, $item->getWarnings());
            }

            if ($item->getSuggestions()) {
                $suggestions = array_merge($suggestions, $item->getSuggestions());
            }
        }

        return [$warnings, $suggestions];
    }
}
