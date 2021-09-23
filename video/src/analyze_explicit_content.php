<?php

/**
 * Copyright 2017 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/video/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 2 || count($argv) > 3) {
    return print("Usage: php analyze_explicit_content.php URI\n");
}
list($_, $uri) = $argv;
$options = isset($argv[2]) ? ['pollingIntervalSeconds' => $argv[2]] : [];

// [START video_analyze_explicit_content]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;
use Google\Cloud\VideoIntelligence\V1\Likelihood;

/** Uncomment and populate these variables in your code */
// $uri = 'The cloud storage object to analyze (gs://your-bucket-name/your-object-name)';
// $options = []; // Optional, can be used to increate "pollingIntervalSeconds"

$video = new VideoIntelligenceServiceClient();

# Execute a request.
$features = [Feature::EXPLICIT_CONTENT_DETECTION];
$operation = $video->annotateVideo($features, [
    'inputUri' => $uri,
]);

# Wait for the request to complete.
$operation->pollUntilComplete($options);

# Print the result.
if ($operation->operationSucceeded()) {
    $results = $operation->getResult()->getAnnotationResults()[0];
    $explicitAnnotation = $results->getExplicitAnnotation();
    foreach ($explicitAnnotation->getFrames() as $frame) {
        $time = $frame->getTimeOffset();
        printf('At %ss:' . PHP_EOL, $time->getSeconds() + $time->getNanos() / 1000000000.0);
        printf('  pornography: ' . Likelihood::name($frame->getPornographyLikelihood()) . PHP_EOL);
    }
} else {
    print_r($operation->getError());
}
// [END video_analyze_explicit_content]
