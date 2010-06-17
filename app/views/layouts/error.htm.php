<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->html->charset() ?>
        <title><?php echo $exception->getMessage() ?> - Spaghetti* Framework</title>
        <style type="text/css">
            body { background: #23201E; font: 14px Helvetica, Arial, sans-serif; margin: 0; padding: 30px; }
            h1 { color: #9c0; font: lighter 36px "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, sans-serif; margin: 0; }
            dl { background: #3D3734; font-size: 16px; padding: 20px; }
            dt { color: #9c0; }
            dd { background: #322D2B; color: #fff; margin: 5px 0 15px; padding: 20px; }
            dd, dl, pre { -moz-border-radius: 4px; -webkit-border-radius: 4px; border-radius: 4px; }
            pre { color: #fff; font: 12px Monaco, Consolas, 'Courier New', monospace; background: #322D2B; margin: 0; overflow: auto; }
        </style>
    </head>
    
    <body>
        <h1><?php echo $exception->getMessage() ?></h1>
        <dl>
            <dt>Details:</dt>
            <dd><?php echo $exception->getMessage() ?></dd>
            <dt>File:</dt>
            <dd><pre><?php echo $exception->getFile() . ':' . $exception->getLine() ?></pre></dd>
            <dt>Stack Trace:</dt>
            <dd><pre><?php echo $exception->getTraceAsString() ?></pre></dd>
        </dl>
    </body>
</html>