#!/usr/bin/env php
<?php

chdir(__DIR__ . '/../../../builder');
passthru('composer install');
passthru('php bin/app.php app:layers:publish');
