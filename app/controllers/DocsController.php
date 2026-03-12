<?php

namespace App\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class DocsController extends Controller
{
    public $enableCsrfValidation = false;

    public function beforeAction($action): bool
    {
        if (YII_ENV !== 'dev') {
            throw new NotFoundHttpException('Not Found');
        }

        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        Yii::$app->response->format = Response::FORMAT_RAW;

        $specUrl = rtrim(Yii::$app->request->hostInfo, '/') . '/docs/openapi.yaml';

        return <<<HTML
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title>Yii2 Livestream API Docs</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css" />
            <style>
                body {
                    margin: 0;
                    background: #fafafa;
                }
                #swagger-ui {
                    max-width: 1200px;
                    margin: 0 auto;
                }
            </style>
        </head>
        <body>
            <div id="swagger-ui"></div>
            <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
            <script>
                window.onload = function() {
                    window.ui = SwaggerUIBundle({
                        url: '{$specUrl}',
                        dom_id: '#swagger-ui',
                        deepLinking: true,
                        presets: [
                            SwaggerUIBundle.presets.apis,
                            SwaggerUIBundle.SwaggerUIStandalonePreset
                        ],
                        layout: 'BaseLayout'
                    });
                };
            </script>
        </body>
        </html>
        HTML;
    }

    public function actionOpenapiYaml(): string
    {
        $specPath = Yii::getAlias('@app/docs/openapi.yaml');
        if (!is_file($specPath)) {
            throw new NotFoundHttpException('OpenAPI spec not found');
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/yaml; charset=UTF-8');

        $content = file_get_contents($specPath);
        if ($content === false) {
            throw new NotFoundHttpException('Unable to read OpenAPI spec');
        }

        return $content;
    }
}
