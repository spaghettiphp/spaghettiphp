<?php echo '<?php' . PHP_EOL ?>

class <?php echo $controller ?>Controller extends AppController {
<?php foreach($actions as $action): ?>
    public function <?php echo $action ?>() {

    }
<?php endforeach ?>
}