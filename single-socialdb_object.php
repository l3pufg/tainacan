<?php
if($post->post_parent === 0) {
    $colModel = new CollectionModel();
    $parent_obj = $colModel->get_collection_by_object($post->ID)[0];
    if( is_object($parent_obj) ) {
        $parent = get_post( $parent_obj->ID );
    }
} else if($post->post_parent > 0) {
    $parent = get_post($post->post_parent);
}

$collection_id = $parent->ID;
$single_mode = get_post_meta($collection_id, 'socialdb_collection_item_visualization', true);

if("one" === $single_mode) {
    include_once(dirname(__FILE__) .  '/controllers/object/object_controller.php');
    $obj = new ObjectController();
    $item_data = ['collection_id' => $collection_id, 'object_id' => $post->ID];
    $op = $obj->operation('list_single_object', $item_data);
    get_header();
    get_template_part("partials/setup","header");
        echo "<section class='container-fluid'> $op </section>";
    get_footer();
    exit();

} else {
    get_header();
    get_template_part("partials/setup","header");

    include_once(dirname(__FILE__) . '/helpers/view_helper.php');
    include_once(dirname(__FILE__) . '/helpers/object/object_helper.php');
    include_once(dirname(__FILE__) . '/views/object/js/list_single_js.php');
    include_once(dirname(__FILE__) . '/models/collection/collection_model.php');

    $metas = get_post_meta($post->ID);
    $object_id = $post->ID;
    $create_perm_object = verify_allowed_action($collection_id, 'socialdb_collection_permission_create_property_object');
    $edit_perm_object = verify_allowed_action($collection_id, 'socialdb_collection_permission_edit_property_object');
    $delete_perm_object = verify_allowed_action($collection_id, 'socialbd_collection_permission_delete_property_object');
    $create_perm_data = verify_allowed_action($collection_id, 'socialdb_collection_permission_create_property_data');
    $edit_perm_data = verify_allowed_action($collection_id, 'socialdb_collection_permission_edit_property_data');
    $delete_perm_data = verify_allowed_action($collection_id, 'socialdb_collection_permission_delete_property_data');

    $meta_type = ucwords($metas['socialdb_object_dc_type'][0]);
    $item_opts = [
        'source' => (key_exists('socialdb_object_dc_source', $metas) ? $metas['socialdb_object_dc_source'][0] : ''),
        'license' => (key_exists('socialdb_license_id', $metas) ? $metas['socialdb_license_id'][0] : -1),
        'from' => (key_exists('socialdb_object_from', $metas) ? $metas['socialdb_object_from'][0] : '')
    ];

    $view_helper = new ObjectHelper($collection_id);
    ?>

    <ol class="breadcrumb item-breadcrumbs">
        <li><a href="<?php echo site_url(); ?>"> Home </a></li>
        <li><a href="<?php echo get_the_permalink($collection_id); ?>"> <?php echo $parent->post_title; ?> </a></li>
        <li class="active"> <?php echo $post->post_title; ?> </li>

        <button data-title="<?php printf(__("URL of %s", "tainacan"), $post->post_title); ?>" id="iframebuttonObject"
                data-container="body"
                class="btn bt-default content-back pull-right" data-toggle="popoverObject" data-placement="left"
                data-content="">
            <span class="glyphicon glyphicon-link"></span>
        </button>
    </ol>

    <div id="single_item_tainacan" class="col-md-12">

        <div id="graph_container"></div>

        <div class="col-md-9 item-main-data row">

            <div class="col-md-12 content-title single-item-title tainacan-header-info no-padding">
                <div class="col-md-10"
                     style="margin-top:14px;" <?php echo $view_helper->get_visibility($view_helper->terms_fixed['title']) ?>>
                    <h3 id="text_title"><?php echo $post->post_title; ?></h3>
                    <span id="event_title" style="display:none;">
                    <input type="text" value="<?php echo $post->post_title; ?>" id="title_field" class="form-control">
                </span>
                    <small>
                        <?php if (verify_allowed_action($collection_id, 'socialdb_collection_permission_edit_property_data_value', $object_id)): ?>
                            <button type="button" title="<?php _e('Cancel modification', 'tainacan') ?>"
                                    onclick="cancel_title()" id="cancel_title" class="btn btn-default btn-xs"
                                    style="display: none;">
                                <span class="glyphicon glyphicon-arrow-left"></span>
                            </button>
                            <button type="button" onclick="edit_title()" id="edit_title" class="btn btn-default btn-xs">
                                <span class="glyphicon glyphicon-edit"></span> <?php viewHelper::render_icon("edit_object"); ?>
                            </button>
                            <button type="button" onclick="save_title('<?php echo $post->ID ?>')" id="save_title"
                                    class="btn btn-default btn-xs" style="display: none;"><span
                                        class="glyphicon glyphicon-floppy-disk"></span></button>
                        <?php endif; ?>
                    </small>
                </div>

                <div class="new-item-actions">
                    <?php include_once dirname(__FILE__) . "/views/object/list_modes/actions/item_actions.php"; ?>
                </div>

                <div class="col-md-12 no-padding" style="margin-top:13.5px;padding-right: 15px;">
                    <hr class="single-item-divider"/>
                </div>

                <div class="col-md-6">
                    <b> <?php echo __('Sent by: ', 'tainacan'); ?> </b> <?php echo get_the_author_meta("display_name", $post->post_author); ?>
                </div>
                <div class="col-md-6" style="text-align: right">
                    <span> <b> <?php _e('Sent date: ', 'tainacan'); ?> </b> <?php echo get_the_date('d/m/y', $post->ID); ?> </span>
                </div>

                <div class="col-md-12" style="padding-bottom: 20px;">
                    <div class="content-wrapper" <?php if (has_action('home_item_content_div')) do_action('home_item_content_div') ?>
                         style="padding: 0; margin-top: 10px;">
                        <div>
                            <?php
                            if ($metas['socialdb_object_dc_type'][0] == 'text') {
                                echo $metas['socialdb_object_content'][0];
                            } else {
                                if ($item_opts['from'] == 'internal' && wp_get_attachment_url($metas['socialdb_object_content'][0])) {
                                    $url = wp_get_attachment_url($metas['socialdb_object_content'][0]);
                                    switch ($metas['socialdb_object_dc_type'][0]) {
                                        case 'audio':
                                            $content = '<audio controls><source src="' . $url . '">' . __('Your browser does not support the audio element.', 'tainacan') . '</audio>';
                                            break;
                                        case 'image':
                                            if (get_the_post_thumbnail($post->ID, 'thumbnail')) {
                                                $url_image = get_the_post_thumbnail($post->ID, 'large', ['class' => 'img-responsive img-thumbnail']);
                                                $style_watermark = ($has_watermark ? 'style="background:url(' . $url_watermark . ') no-repeat center; background-size: contain;"' : '');
                                                $opacity_watermark = ($has_watermark ? 'opacity: 0.80;' : '');
                                                $content = '<center ' . $style_watermark . '>' . $url_image . '</center>';
                                            }
                                            break;
                                        case 'video':
                                            $content = '<video width="400" controls><source src="' . $url . '">' . __('Your browser does not support HTML5 video.', 'tainacan') . '</video>';
                                            break;
                                        case 'pdf':

                                            $view = get_template_directory_uri() . '/libraries/js/pdfThumb/pdfJS/web/viewer.html?file=' . $url;
                                            $iframe_script = "";
                                            $content =
                                                "
                                             <script>
                                                hide_pdf_viewer_buttons();
                                             </script>
                                             <iframe id='iframePDF' name='iframePDF' src='$view' height='500px' allowfullscreen webkitallowfullscreen>
                                                        
                                             </iframe>";

                                            //'<embed src="' . $url . '" width="600" height="500" alt="pdf" pluginspage="http://www.adobe.com/products/acrobat/readstep2.html">';
                                            break;
                                        default:
                                            $content = '<p style="text-align:center;">' . __('File link:') . ' <a target="_blank" href="' . $url . '">' . __('Click here!', 'tainacan') . '</a></p>';
                                            break;
                                    }
                                } else {
                                    switch ($metas['socialdb_object_dc_type'][0]) {
                                        case 'audio':
                                            $content = '<audio controls><source src="' . $metas['socialdb_object_content'][0] . '">' . __('Your browser does not support the audio element.', 'tainacan') . '</audio>';
                                            break;
                                        case 'image':
                                            $style_watermark = ($has_watermark ? 'style="background:url(' . $url_watermark . ') no-repeat center; background-size: contain;"' : '');
                                            $opacity_watermark = ($has_watermark ? 'opacity: 0.80;' : '');
                                            if (get_the_post_thumbnail($post->ID, 'thumbnail')) {
                                                $url_image = wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'large'));
                                                $content = '<center ' . $style_watermark . '><img style="max-width:480px; ' . $opacity_watermark . '"  src="' . $url_image . '" class="img-responsive" /></center>';
//                                            $content = '<center><a href="#" onclick="$.prettyPhoto.open([\'' . $url_image . '\'], [\'\'], [\'\']);return false">
//                                                        <img style="max-width:880px;"  src="' . $url_image . '" class="img-responsive" />
//                                                    </a></center>';
                                            } else {
                                                $content = "<img src='" . $metas['socialdb_object_content'][0] . "' class='img-responsive' />";
                                            }
                                            break;
                                        case 'video':
                                            if (strpos($metas['socialdb_object_content'][0], 'youtube') !== false) {
                                                $step1 = explode('v=', $metas['socialdb_object_content'][0]);
                                                $step2 = explode('&', $step1[1]);
                                                $video_id = $step2[0];
                                                $content = "<div style='height:600px; display: flex !important;'  ><iframe  class='embed-responsive-item' src='https://www.youtube.com/embed/" . $video_id . "?html5=1' allowfullscreen frameborder='0'></iframe></div>";
                                            } elseif (strpos($metas['socialdb_object_content'][0], 'vimeo') !== false) {
                                                $step1 = explode('/', rtrim($metas['socialdb_object_content'][0], '/'));
                                                $video_id = end($step1);
                                                $content = "<div class=\"embed-responsive embed-responsive-16by9\"><iframe class='embed-responsive-item' src='https://player.vimeo.com/video/" . $video_id . "' frameborder='0'></iframe></div>";
                                            } else {
                                                $content = "<div class=\"embed-responsive embed-responsive-16by9\"><iframe class='embed-responsive-item' src='" . $metas['socialdb_object_content'][0] . "' frameborder='0'></iframe></div>";
                                            }
                                            break;
                                        case 'pdf':
                                            $content = '<embed src="' . $metas['socialdb_object_content'][0] . '" width="600" height="500" alt="pdf" pluginspage="http://www.adobe.com/products/acrobat/readstep2.html">';
                                            break;
                                        default:
                                            $content = '<p style="text-align:center;">' . __('File link:', 'tainacan') . ' <a target="_blank" href="' . $metas['socialdb_object_content'][0] . '">' . __('Click here!', 'tainacan') . '</a></p>';
                                            break;
                                    }
                                }
                                echo $content;
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-12 content-title single-item-title tainacan-fixed-metas">

                <div class="col-md-12 item-fixed-data no-padding">
                    <div class="col-md-6 left-container no-padding">
                        <div class="item-source box-item-paddings" <?php echo $view_helper->get_visibility($view_helper->terms_fixed['source']) ?>
                             style="border-top:none">
                            <div class="row" <?php if (has_action('home_item_source_div')) do_action('home_item_source_div') ?>
                                 style="padding-left: 30px;">
                                <div class="col-md-12 no-padding">
                                    <h4 class="title-pipe single-title"> <?php echo ($view_helper->terms_fixed['source']) ? $view_helper->terms_fixed['source']->name : _e('Source', 'tainacan') ?> </h4>
                                    <div class="edit-field-btn">
                                        <?php
                                        // verifico se o metadado pode ser alterado
                                        if (verify_allowed_action($collection_id, 'socialdb_collection_permission_edit_property_data_value', $object_id)):
                                            ?>
                                            <small>
                                                <button type="button" onclick="cancel_source()" id="cancel_source"
                                                        class="btn btn-default btn-xs" style="display: none;"><span
                                                            class="glyphicon glyphicon-arrow-left"></span></button>
                                                <button type="button" onclick="edit_source()" id="edit_source"
                                                        class="btn btn-default btn-xs"><span
                                                            class="glyphicon glyphicon-edit"></span></button>
                                                <button type="button" onclick="save_source('<?php echo $post->ID ?>')"
                                                        id="save_source" class="btn btn-default btn-xs"
                                                        style="display: none;"><span
                                                            class="glyphicon glyphicon-floppy-disk"></span></button>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div id="text_source"> <?php echo format_item_text_source($item_opts['source']); ?> </div>
                                    <div id="event_source" style="display:none;">
                                        <input type="text" class="form-control" id="source_field"
                                               value="<?php echo $item_opts['source']; ?>"
                                               name="source_field"
                                               placeholder="<?php _e('Type the source and click save!') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item-type box-item-paddings" <?php echo $view_helper->get_visibility($view_helper->terms_fixed['type']) ?>
                             style="border-top: none">
                            <div class="row" <?php if (has_action('home_item_type_div')) do_action('home_item_type_div') ?>
                                 style="padding-left: 30px;">
                                <div class="col-md-12 no-padding">
                                    <h4 class="title-pipe single-title"> <?php _e('Type', 'tainacan'); ?></h4>
                                    <div class="edit-field-btn">
                                        <?php
                                        // verifico se o metadado pode ser alterado
                                        if (verify_allowed_action($collection_id, 'socialdb_collection_permission_edit_property_data_value', $object_id)): ?>
                                            <small>
                                                <button type="button" onclick="cancel_type()" id="cancel_type"
                                                        class="btn btn-default btn-xs" style="display: none;"><span
                                                            class="glyphicon glyphicon-arrow-left"></span></button>
                                                <button type="button" onclick="edit_type()" id="edit_type"
                                                        class="btn btn-default btn-xs"><span
                                                            class="glyphicon glyphicon-edit"></span></button>
                                                <button type="button" onclick="save_type('<?php echo $post->ID ?>')"
                                                        id="save_type" class="btn btn-default btn-xs"
                                                        style="display: none;"><span
                                                            class="glyphicon glyphicon-floppy-disk"></span></button>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div id="text_type">
                                        <?php _e($meta_type, 'tainacan') ?>
                                    </div>
                                    <div id="event_type" style="display:none;">
                                        <input type="radio" value="text"
                                            <?php echo ($meta_type == 'Text') ? 'checked="checked"' : '' ?>
                                               name="type_field"><?php _e('Text', 'tainacan') ?><br>
                                        <input type="radio" value="image"
                                            <?php echo ($meta_type == 'Image') ? 'checked="checked"' : '' ?>
                                               name="type_field"><?php _e('Image', 'tainacan') ?><br>
                                        <input type="radio" value="audio"
                                            <?php echo ($meta_type == 'Audio') ? 'checked="checked"' : '' ?>
                                               name="type_field"><?php _e('Audio', 'tainacan') ?><br>
                                        <input type="radio" value="video"
                                            <?php echo ($meta_type == 'Video') ? 'checked="checked"' : '' ?>
                                               name="type_field"><?php _e('Video', 'tainacan') ?><br>
                                        <input type="radio" value="pdf"
                                            <?php echo ($meta_type == 'Pdf') ? 'checked="checked"' : '' ?>
                                               name="type_field"><?php _e('PDF', 'tainacan') ?><br>
                                        <input type="radio" value="other"
                                            <?php echo ($meta_type == 'Other') ? 'checked="checked"' : '' ?>
                                               name="type_field"><?php _e('Other', 'tainacan') ?><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item-thumb box-item-paddings" <?php echo $view_helper->get_visibility($view_helper->terms_fixed['thumbnail']) ?>
                             style="border-top: none; border-bottom: none">
                            <div class="content-thumb" style="padding-left: 15px; ">
                                <h4 class="title-pipe single-title">  <?php echo ($view_helper->terms_fixed['thumbnail']) ? $view_helper->terms_fixed['thumbnail']->name : _e('Thumbnail', 'tainacan'); ?></h4>
                                <div class="edit-field-btn">
                                    <?php
                                    // Evento para alteracao do thumbnail de um item
                                    // verifico se o metadado pode ser alterado
                                    if (verify_allowed_action($collection_id, 'socialdb_collection_permission_edit_property_data_value', $object_id)): ?>
                                        <div style="margin-top: 5px;">
                                            <button type="button" onclick="edit_thumbnail()" id="edit_thumbnail"
                                                    class="btn btn-default btn-xs"><span
                                                        class="glyphicon glyphicon-edit"></span></button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if (get_the_post_thumbnail($post->ID, 'thumbnail')) {
                                        $url_image = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
                                        echo get_the_post_thumbnail($post->ID, 'thumbnail');
                                    } else { ?>
                                        <img class="img-responsive"
                                             src="<?php echo get_item_thumbnail_default($post->ID); ?>" width="45%"/>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 right-container" style="margin-left: 0;">
                        <div class="item-ranking box-item-paddings box-item-right" style="border-top:none">
                            <h4 class="title-pipe single-title"> <?php _e('Ranking', 'tainacan'); ?></h4>
                            <div id="single_list_ranking_<?php echo $post->ID; ?>" class="row"></div>
                        </div>
                        <div class="item-share box-item-paddings box-item-right">
                            <h4 class="title-pipe single-title"> <?php _e('Sharing', 'tainacan'); ?></h4>

                            <div class="content-redesocial-NO" style="width: 100%">
                                <a class="fb" target="_blank"
                                   href="http://www.facebook.com/sharer/sharer.php?s=100&amp;p[url]=<?php echo get_the_permalink($collection_id) . '?item=' . $post->post_name; ?>&amp;p[images][0]=<?php echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)); ?>&amp;p[title]=<?php echo htmlentities($post->post_title); ?>&amp;p[summary]=<?php echo strip_tags($post->post_content); ?>">
                                    <img src="<?php echo get_template_directory_uri() . '/libraries/images/icons/icon-facebook.png'; ?>"/>
                                </a>
                                <a class="twitter" target="_blank"
                                   href="https://twitter.com/intent/tweet?url=<?php echo get_the_permalink($collection_id) . '?item=' . $post->post_name; ?>&amp;text=<?php echo htmlentities($post->post_title); ?>&amp;via=socialdb">
                                    <img src="<?php echo get_template_directory_uri() . '/libraries/images/icons/icon-twitter.png'; ?>"/>
                                </a>
                                <a class="gplus" target="_blank"
                                   href="https://plus.google.com/share?url=<?php echo get_the_permalink($collection_id) . '?item=' . $post->post_name; ?>">
                                    <img src="<?php echo get_template_directory_uri() . '/libraries/images/icons/icon-googleplus.png'; ?>"/>
                                </a>

                                <div class="menu-hover-container">

                                    <a href="javascript:void(0)" class="data-share dropdown-toggle"
                                       data-toggle="dropdown" role="button" aria-expanded="false">
                                        <!-- <div style="font-size:1em; cursor:pointer; color: black; display: inline-block;" data-icon="&#xe00b;"></div> -->
                                        <img src="<?php echo get_template_directory_uri() . '/libraries/images/icons/icon-share.png'; ?>"/>
                                    </a>
                                    <ul style=" z-index: 9999;" class="dropdown-menu submenu-hover-open" role="menu">
                                        <li>
                                            <a target="_blank"
                                               href="<?php echo get_the_permalink($collection_id) . '?item=' . $post->post_name; ?>.rdf"><span
                                                        class="glyphicon glyphicon-upload"></span> <?php _e('RDF', 'tainacan'); ?>
                                                &nbsp;
                                            </a>
                                        </li>
                                        <?php if (is_restful_active()): ?>
                                            <li>
                                                <a href="<?php echo site_url() . '/wp-json/posts/' . $post->ID . '/?type=socialdb_object' ?>"><span
                                                            class="glyphicon glyphicon-upload"></span> <?php _e('JSON', 'tainacan'); ?>
                                                    &nbsp;
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <a onclick="showGraph('<?php echo get_the_permalink($collection_id) . '?item=' . $post->post_name; ?>.rdf')"
                                               style="cursor: pointer;">
                                                <span class="glyphicon glyphicon-upload"></span> <?php _e('Graph', 'tainacan'); ?>
                                                &nbsp;
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>

                        <?php
                        $mapping = get_option('socialdb_general_mapping_collection');
                        if (has_action("add_barcode") && $mapping['Exemplares'] == $collection_id)
                            do_action("add_barcode", $collection_id, $post->ID);
                        ?>
                    </div>
                </div>

            </div>

            <div class="col-md-12 item-metadata no-padding">

                <div class="item-description box-item-paddings" <?php echo $view_helper->get_visibility($view_helper->terms_fixed['description']) ?>>
                    <h4 class="title-pipe single-title"><?php echo ($view_helper->terms_fixed['description']) ? $view_helper->terms_fixed['description']->name : _e('Description', 'tainacan'); ?> </h4>
                    <div class="edit-field-btn">
                        <?php
                        // verifico se o metadado pode ser alterado
                        if (verify_allowed_action($collection_id, 'socialdb_collection_permission_edit_property_data_value', $object_id)):
                            ?>
                            <small>
                                <button type="button" onclick="cancel_description()" id="cancel_description"
                                        class="btn btn-default btn-xs" style="display: none;"><span
                                            class="glyphicon glyphicon-arrow-left"></span></button>
                                <button type="button" onclick="edit_description()" id="edit_description"
                                        class="btn btn-default btn-xs"><span class="glyphicon glyphicon-edit"></span>
                                </button>
                                <button type="button" onclick="save_description('<?php echo $post->ID ?>')"
                                        id="save_description" class="btn btn-default btn-xs" style="display: none;">
                                    <span class="glyphicon glyphicon-floppy-disk"></span></button>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div id="text_description">
                        <div style="white-space: pre-wrap;"><?php echo $post->post_content; ?></div>
                    </div>
                    <div id="event_description" style="display:none; min-height: 150px;">
                        <textarea class="col-md-12 form-control" id="description_field"
                                  style="width:100%; min-height: 150px;"><?php echo $post->post_content; ?></textarea>
                    </div>
                </div>

                <div class="col-md-6 left-container" <?php echo $view_helper->get_visibility($view_helper->terms_fixed['license']) ?>
                     style="border-right: 3px solid #e8e8e8">
                    <!-- Licencas do item -->
                    <div class="box-item-paddings item-license" <?php if (has_action('home_item_license_div')) do_action('home_item_license_div') ?>
                         style="border: none;">
                        <h4 class="title-pipe single-title"> <?php _e('License', 'tainacan'); ?></h4>
                        <div class="edit-field-btn">
                            <?php
                            // verifico se o metadado pode ser alterado
                            if (verify_allowed_action($collection_id, 'socialdb_collection_permission_edit_property_data_value', $object_id)): ?>
                                <small>
                                    <button type="button" onclick="cancel_license()" id="cancel_license"
                                            class="btn btn-default btn-xs" style="display: none;"><span
                                                class="glyphicon glyphicon-arrow-left"></span></button>
                                    <button type="button" onclick="edit_license()" id="edit_license"
                                            class="btn btn-default btn-xs"><span
                                                class="glyphicon glyphicon-edit"></span></button>
                                    <button type="button" onclick="save_license('<?php echo $post->ID ?>')"
                                            id="save_license" class="btn btn-default btn-xs" style="display: none;">
                                        <span class="glyphicon glyphicon-floppy-disk"></span></button>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div id="text_license">
                            <p>
                                <?php if (is_object(get_post($item_opts['license'])) && isset(get_post($item_opts['license'])->post_title))
                                    echo get_post($item_opts['license'])->post_title;
                                else
                                    _t('No license registered for this item', 1); ?>
                            </p>
                        </div>
                        <div id="event_license" style="display: none;">
                        </div>
                    </div>
                </div>

                <div class="col-md-6 right-container no-padding">
                    <!-- Tags -->
                    <div class="box-item-paddings item-tags" <?php echo $view_helper->get_visibility($view_helper->terms_fixed['tags']) ?> <?php if (has_action('home_item_tag_div')) do_action('home_item_tag_div') ?>>
                        <h4 class="title-pipe single-title"> <?php _e('Tags', 'tainacan'); ?></h4>
                        <div class="edit-field-btn">
                            <?php
                            // verifico se o metadado pode ser alterado
                            if (verify_allowed_action($collection_id, 'socialdb_collection_permission_edit_tag', $post->ID)):
                                ?>
                                <button type="button" onclick="cancel_tag()" id="cancel_tag"
                                        class="btn btn-default btn-xs" style="display: none;"><span
                                            class="glyphicon glyphicon-arrow-left"></span></button>
                                <button type="button" onclick="edit_tag()" id="edit_tag" class="btn btn-default btn-xs">
                                    <span class="glyphicon glyphicon-edit"></span></button>
                                <button type="button" onclick="save_tag('<?php echo $post->ID ?>')" id="save_tag"
                                        class="btn btn-default btn-xs" style="display: none;"><span
                                            class="glyphicon glyphicon-floppy-disk"></span></button>
                            <?php endif; ?>
                        </div>

                        </h4>
                        <div>
                            <center>
                                <button id="single_show_classificiations_<?php echo $post->ID; ?>"
                                        onclick="show_classifications_single('<?php echo $post->ID; ?>')"
                                        class="btn btn-default btn-lg"><?php _e('Show classifications', 'tainacan'); ?></button>
                            </center>
                            <div id="single_classifications_<?php echo $post->ID ?>">
                            </div>
                            <div id="event_tag" style="display:none;">
                                <input type="text" style="width:50%;" class="form-control col-md-6" id="event_tag_field"
                                       placeholder="<?php _e('Type the tag name', 'tainacan') ?>">
                            </div>
                            <script>
                                $('#single_show_classificiations_<?php echo $post->ID ?>').hide().trigger('click');
                            </script>
                        </div>
                    </div>
                </div>


                <!-- Metadados do item -->
                <div class="col-md-12 all-metadata no-padding">
                    <div>
                        <div class="meta-header" style="padding: 10px 20px 10px 20px">
                            <h4 class="title-pipe single-title"> <?php _e('Properties', 'tainacan') ?></h4>
                            <div <?php do_action('home_item_add_property') ?> class="btn-group edit-field-btn">
                                <?php if ($create_perm_object || $create_perm_data): ?>
                                    <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"
                                            id="btnGroupVerticalDrop1" style="font-size:11px;">
                                        <span class="glyphicon glyphicon-plus grayleft"></span> <span
                                                class="caret"></span>
                                    </button>
                                    <ul aria-labelledby="btnGroupVerticalDrop1" role="menu"
                                        class="dropdown-menu add-metadata">
                                        <?php if ($create_perm_data): ?>
                                            <li>&nbsp;<span
                                                        class="glyphicon glyphicon-th-list graydrop"></span>&nbsp;<span><a
                                                            class="add_property_data"
                                                            onclick="show_form_data_property_single('<?php echo $post->ID ?>')"
                                                            href="#property_form_<?php echo $post->ID ?>"><?php _e('Add new data property', 'tainacan'); ?></a></span>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($create_perm_object): ?>
                                            <li>&nbsp;<span
                                                        class="glyphicon glyphicon-th-list graydrop"></span>&nbsp;<span><a
                                                            class="add_property_object"
                                                            onclick="show_form_object_property_single('<?php echo $post->ID ?>')"
                                                            href="#property_form_<?php echo $post->ID ?>"><?php _e('Add new object property', 'tainacan'); ?></a></span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                            <div <?php do_action('home_item_delete_property') ?> class="btn-group edit-field-btn">
                                <?php if ($edit_perm_object || $delete_perm_object || $edit_perm_data || $delete_perm_data): ?>
                                    <button onclick="list_properties_edit_remove_single($('#single_object_id').val())"
                                            data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"
                                            id="btnGroupVerticalDrop2" style="font-size:11px;">
                                        <span class="glyphicon glyphicon-pencil grayleft"></span>
                                        <span class="caret"></span>
                                    </button>
                                    <ul id="single_list_properties_edit_remove" aria-labelledby="btnGroupVerticalDrop1"
                                        role="menu" class="dropdown-menu">
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <div id="single_list_all_properties_<?php echo $post->ID ?>" class="single_list_properties">
                                <center><img width="100" heigth="100"
                                             src="<?php echo get_template_directory_uri() . '/libraries/images/catalogo_loader_725.gif' ?>"><?php _e('Loading metadata for this item', 'tainacan') ?>
                                </center>
                            </div>
                            <div id="single_data_property_form_<?php echo $post->ID ?>"></div>
                            <div id="single_object_property_form_<?php echo $post->ID ?>"></div>
                            <div id="single_edit_data_property_form_<?php echo $post->ID ?>"></div>
                            <div id="single_edit_object_property_form_<?php echo $post->ID ?>"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-12 item-comments no-padding">
                <div>
                    <div id="comments_object"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 item-attachments">
            <div <?php if (has_action('home_item_attachments_div')) do_action('home_item_attachments_div') ?> >
                <div id="single_list_files_<?php echo $post->ID ?>"></div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
    </div>

    <!-- Modal para upload de thumbnail -->
    <div class="modal fade" id="single_modal_thumbnail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formThumbnail">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel"><span
                                    class="glyphicon glyphicon-trash"></span>&nbsp;<?php _e('Select a image', 'tainacan'); ?>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <input type="file" class="form-control" id="thumbnail_field" name="attachment">
                        <input type="hidden" name="operation" value="insert_attachment">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal"><?php echo __('Close', 'tainacan'); ?></button>
                        <button type="submit"
                                class="btn btn-primary"><?php echo __('Alter Image', 'tainacan'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade modal-share-network" id="modal_share_network_item<?php echo $post->ID ?>" tabindex="-1"
         role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <?php echo $view_helper->render_modal_header('remove-sign', '<span class="glyphicon glyphicon-share"></span> ', __('Share', 'tainacan')); ?>

                <div class="modal-body">
                    <form name="form_share_item<?php echo get_the_ID() ?>"
                          id="form_share_item<?php echo get_the_ID() ?>" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <?php _t('Post it on: ', 1); ?><br>
                                <a target="_blank"
                                   href="https://twitter.com/intent/tweet?url=<?php echo get_the_permalink($collection_id) . '?item=' . $post->post_name; ?>&amp;text=<?php echo htmlentities(get_the_title()); ?>&amp;via=socialdb">
                                    <?php echo ViewHelper::render_icon('twitter-square', 'png', 'Twitter'); ?>
                                </a>

                                <a onclick="redirect_facebook('<?php echo get_the_ID() ?>');" href="javascript:void(0)">
                                    <?php echo ViewHelper::render_icon('facebook-square', 'png', 'Facebook'); ?>
                                </a>

                                <a target="_blank"
                                   href="https://plus.google.com/share?url=<?php echo get_the_permalink($collection_id) . '?item=' . $post->post_name; ?>">
                                    <?php echo ViewHelper::render_icon('googleplus-square', 'png', 'Google Plus'); ?>
                                </a>

                                <br> <br>
                                <?php _t('Link: ', 1); ?>
                                <input type="text" id="link_object_share<?php echo get_the_ID() ?>" class="form-control"
                                       value="<?php echo get_the_permalink($collection_id) . '?item=' . $post->post_name; ?>"/>
                            </div>
                            <div class="col-md-6">
                                <?php _t('Embed it: ', 1); ?>
                                <textarea id="embed_object<?php echo get_the_ID() ?>" class="form-control"
                                          rows="5"><?php echo '<iframe width="1024" height="768" src="' . get_the_permalink($collection_id) . '?item=' . $post->post_name . '" frameborder="0"></iframe>'; ?></textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <?php _t('Email: ', 1); ?><br>
                                <input type="text" id="email_object_share<?php echo get_the_ID() ?>"
                                       class="form-control"/><br>
                                <input type="hidden" name="collection_id"
                                       id="collections_object_share<?php echo get_the_ID() ?>_id">
                                <input type="hidden" name="collection_id"
                                       id="collections_object_share<?php echo get_the_ID() ?>_url">
                            </div>
                        </div>
                    </form>
                </div>

                <?php echo $view_helper->render_modal_footer("send_share_item(\"$curr_id\")", __('Send', 'tainacan')); ?>

            </div>
        </div>
    </div>

    <?php
    get_footer();
}
