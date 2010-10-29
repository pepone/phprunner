<?php

// *****************************************************************************
//
// Copyright (c) 2010 José Manuel, Gutiérrez de la Concha. All rights reserved.
//
// This copy of PhpRunner is licensed to you under the terms described
// in the LICENSE file included in this distribution.
//
// email: pepone.onrez@gmail.com
//
// *****************************************************************************

require_once dirname(__FILE__) . '/../oz-web/src/core/ArrayUtil.php';

class DemoScript
{
    public function run()
    {

        $args = getopt("d:", array("duration:"));

        $duration = ArrayUtil::getWithDefault($args, 'd', '');
        if($duration == '')
        {
            $duration = ArrayUtil::getWithDefault($args, 'duration');
        }
        if($duration == false || $duration == '')
        {
            echo "missing duration use -d or --duration to set it\n";
            exit(-1);
        }

        $time = 0;
        $sleep = 10;

        while($time < $duration)
        {
            sleep($sleep);
            $time += $sleep;
            error_log('DemoScript running during: "' . $time . '" seconds');
        }
        error_log('DemoScript end');
    }
}

$s = new DemoScript();
$s->run();