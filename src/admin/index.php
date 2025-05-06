<?php
include 'config.php';
include $tpl . 'header.php';
?>
<div>
    <button type="button" class="btn btn-primary mb-2">Primary</button>
    <?php
    include 'includes/functions/lang.php';

    echo '<h1>' . lang('WELCOME') . '</h1>';
    echo '<a href="#">' . lang('CART') . '</a>'; ?>


</div>
<?php include $tpl . 'footer.php' ?>
