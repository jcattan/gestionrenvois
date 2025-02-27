<?php
if(empty($showtabs)) {
    $showtabs = false;
}
if(empty($active)) {
    $active = 'general'; // Valeur par défaut si $active n'est pas défini
}
?>

<div class="display no-border">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="fpbx-container">
                    <form class="fpbx-submit" name="frm_<?php echo $display; ?>" action="config.php" method="post" <?php if(!empty($jsfuncs['onsubmit'])) echo 'onsubmit="'.$jsfuncs['onsubmit'].'"'; ?> >
                        <input type="hidden" name="display" value="<?php echo $display; ?>">
                        <?php foreach($hiddens as $hidden) { echo $hidden['html']; } ?>
                        <?php if(!empty($action)) { ?>
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php } ?>

                        <?php if($showtabs) { ?>
                            <div class="nav-container">
                                <ul class="nav nav-tabs" role="tablist">
                                    <?php foreach($html['middle'] as $category => $sections) { ?>
                                        <li data-name="<?php echo $category; ?>" class="<?php echo ($category == $active ? 'active' : '')?>">
                                            <a href="#<?php echo $category; ?>" aria-controls="<?php echo $category; ?>" role="tab" data-toggle="tab">
                                                <?php echo $tabtranslations[$category]; ?>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>

                        <div class="tab-content display">
                            <?php if(!empty($html['top'])) { ?>
                                <div class="tab-pane display" id="top">
                                    <?php foreach($html['top'] as $item) { 
                                        switch($item['type']) {
                                            case 'html':
                                            case 'heading':
                                            case 'subheading':
                                                echo $item['html'];
                                                break;
                                            default:
                                                echo '<div class="element-container">';
                                                echo '<div class="row">';
                                                if(!empty($item['prompttext'])) {
                                                    echo '<div class="col-md-3">';
                                                    echo '<label class="control-label">' . $item['prompttext'] . '</label>';
                                                    if(!empty($item['helptext'])) {
                                                        echo '<i class="fa fa-question-circle fpbx-help-icon" data-for="' . $item['name'] . '"></i>';
                                                    }
                                                    echo '</div>';
                                                    echo '<div class="col-md-9">';
                                                } else {
                                                    echo '<div class="col-md-12">';
                                                }
                                                echo $item['html'];
                                                echo '</div></div></div>';
                                        }
                                    } ?>
                                </div>
                            <?php } ?>

                            <?php if(!empty($html['middle'])) { ?>
                                <?php foreach($html['middle'] as $category => $sections) { ?>
                                    <div role="tabpanel" id="<?php echo $category; ?>" class="tab-pane <?php echo ($category == $active ? 'active' : '')?>">
                                        <?php foreach($sections as $section => $items) { ?>
                                            <div class="section-title" data-for="<?php echo $section; ?>">
                                                <h3><i class="fa fa-minus"></i> <?php echo $section; ?></h3>
                                            </div>
                                            <div class="section" data-id="<?php echo $section; ?>">
                                                <?php foreach($items as $item) { 
                                                    switch($item['type']) {
                                                        case 'html':
                                                        case 'heading':
                                                        case 'subheading':
                                                            echo $item['html'];
                                                            break;
                                                        default:
                                                            echo '<div class="element-container">';
                                                            echo '<div class="row">';
                                                            if(!empty($item['prompttext'])) {
                                                                echo '<div class="col-md-3">';
                                                                echo '<label class="control-label">' . $item['prompttext'] . '</label>';
                                                                if(!empty($item['helptext'])) {
                                                                    echo '<i class="fa fa-question-circle fpbx-help-icon" data-for="' . $item['name'] . '"></i>';
                                                                }
                                                                echo '</div>';
                                                                echo '<div class="col-md-9">';
                                                            } else {
                                                                echo '<div class="col-md-12">';
                                                            }
                                                            echo $item['html'];
                                                            echo '</div></div></div>';
                                                    }
                                                } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>

                            <?php if(!empty($html['bottom'])) { ?>
                                <div class="tab-pane display" id="bottom">
                                    <?php foreach($html['bottom'] as $item) { 
                                        switch($item['type']) {
                                            case 'html':
                                            case 'heading':
                                            case 'subheading':
                                                echo $item['html'];
                                                break;
                                            default:
                                                echo '<div class="element-container">';
                                                echo '<div class="row">';
                                                if(!empty($item['prompttext'])) {
                                                    echo '<div class="col-md-3">';
                                                    echo '<label class="control-label">' . $item['prompttext'] . '</label>';
                                                    if(!empty($item['helptext'])) {
                                                        echo '<i class="fa fa-question-circle fpbx-help-icon" data-for="' . $item['name'] . '"></i>';
                                                    }
                                                    echo '</div>';
                                                    echo '<div class="col-md-9">';
                                                } else {
                                                    echo '<div class="col-md-12">';
                                                }
                                                echo $item['html'];
                                                echo '</div></div></div>';
                                        }
                                    } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(!empty($jsfuncs)) { ?>
<script>
<?php 
foreach($jsfuncs as $function => $content) {
    if($function != 'onsubmit') {
        echo "function $function {\n";
        echo $content;
        echo "}\n";
    }
}
?>
</script>
<?php } ?>