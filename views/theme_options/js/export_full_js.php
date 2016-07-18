<script>
    $(function () {
        change_breadcrumbs_title('<?php _e('Export','tainacan') ?>');

        $('#validate_url_container').show('slow');
        
        var src = $('#src').val();
        $('#collection_import_id').val($('#collection_id').val());
        $('#collection_import_csv_id').val($('#collection_id').val());
        $('#collection_id_export_csv').val($('#collection_id').val());
        $('#collection_id_zip').val($('#collection_id').val());

        
        $('#click_zip').click(function (e) {
            e.preventDefault();
            $(this).tab('show')
        });

        $('#form_default').submit(function (e) {
            e.preventDefault();
            $.ajax({
                url: $("#src").val() + '/controllers/mapping/mapping_controller.php',
                type: 'POST',
                data: {operation: 'form_default_mapping', mapping_id: $('input[name=socialdb_default_mapping]:checked').val(), collection_id: $("#collection_id").val()}
            }).done(function (result) {
                elem = jQuery.parseJSON(result);
                console.log(elem);
                if (elem.result === '1') {
                    listTableOAIPMHDC();
                    $("#oai_repository").html(elem.html);
                    showAlertGeneral(elem.title, elem.msg, elem.type);
                } else {
                    showAlertGeneral(elem.title, elem.msg, elem.type);
                }
            });

        });

    });

    function get_icon(icon) {
        var path = '<?php echo get_template_directory_uri() ?>';
        return path + "/libraries/images/icons/icon-" + icon;
    }
    var edit_icon = get_icon("edit.png");
    var delete_icon = get_icon("delete_collection_redirect.png");

    $('#formCsv').submit(function (e) {
        $.ajax({
            url: $('#src').val() + '/controllers/import/csv_controller.php',
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false
        }).done(function (result) {
            try {
                elem = jQuery.parseJSON(result);
                if (elem.error) {
                    showAlertGeneral('<?php _e('Error!', 'tainacan'); ?>', elem.msg, 'error');
                }
            }
            catch (e)
            {
                $('#importForm_csv').show();
                $('#validate_url_csv_container').hide();
                $('#maping_container_csv').html(result);
            }
        });
        e.preventDefault();

    });
    
    function export_csv_file() {
        $.ajax({
            type: "POST",
            url: $('#src').val() + "/controllers/export/export_controller.php",
            data: {
                collection_id: $('#collection_id').val(),
                operation: 'export_csv_file'
            }
        }).done(function (result) {
            showAlertGeneral('<?php _e('Attention', 'tainacan') ?>', '<?php _e('All objects imported succesfully!', 'tainacan') ?>', 'success');
        }).fail(function (jqXHR, textStatus, errorThrown) {

        });

    }

    function verify_delimiter() {
        if ($("#socialdb_delimiter_csv").val().trim() == '') {
            showAlertGeneral('<?php _e('Attention', 'tainacan') ?>', '<?php _e('Please, fill the delimiter correctly!', 'tainacan') ?>', 'error');
            return false;
        } else {
            return true;
        }
    }

</script>