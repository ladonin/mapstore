<?php
/*
 * Сгенерированная анонимная функция для работы с формой добавления/удаления/редактирования меток
 */
use \components\app as components;

$config = self::get_config();
$form_new_point_model = components\Map::get_form_model('new_point');
?>
<script type="text/javascript">
    $(document).ready(function () {
        my_<?php echo($form_new_point_model::FORM_NAME); ?>_object = (function () {

            // Данные формы
            var form_name = '<?php echo(my_pass_through(@$form_new_point_model::model()->get_id())); ?>';
            var form_id = '<?php echo(my_pass_through(@$form_new_point_model::model()->get_id())); ?>';
            var form = $('#' + form_id);
            var photos_input = $('#<?php echo(my_pass_through(@$form_new_point_model::model()->get_id('photos'))); ?>');
            var x_input = $('#<?php echo(my_pass_through(@$form_new_point_model::model()->get_id('x'))); ?>');
            var y_input = $('#<?php echo(my_pass_through(@$form_new_point_model::model()->get_id('y'))); ?>');
            var delete_existing_photo_checkbox_selector = 'form#' + form_id + ' .delete_existing_photo_checkbox';
            var add_new_point_form_photos_selector = '#add_new_point_form_photos';
            
            // Максимальное количество загружаемых фотографий
            var max_photos_per_point = <?php echo(my_pass_through(@$config['allows']['max_upload_files_per_point'])); ?>;

            // Вспомогательная переменная
            var send_status = false;

            // Загружаемые фотографии метки
            var images_for_uploading = new Object();

            // Проверка формы перед отправкой на сервер
            var check = function () {
                
                // При повторном нажатии на кнопку 
                if (send_status === true) {
                    return false;
                }

                if (is_all_images_uploaded() == false) {
                    my_get_message('<?php echo(my_pass_through(@self::trace('errors/new_point/photos_uploaded_yet'))); ?>', 'error');
                    return false;
                }

                if (my_map_vendor.is_redacted() === true) {
                    var count_deleted_photos = ($(delete_existing_photo_checkbox_selector + ':checked').length);
                    var count_existed_photos = ($(delete_existing_photo_checkbox_selector).length);

                    var count_new_photos = 0;
                    // В любом случае (ничего нет или один элемент есть) всегда вернет массив с одним элементом и count=1,
                    // Поэтому сначала проверяем что в массиве
                    if ($.trim($(add_new_point_form_photos_selector).val()).split(' ')[0] != '') {
                        count_new_photos = $.trim($(add_new_point_form_photos_selector).val()).split(' ').length;
                    }

                    var count_current_photos = count_existed_photos + count_new_photos - count_deleted_photos;

                    if (count_current_photos === 0) {
                        my_get_message('<?php echo(my_pass_through(@self::trace('errors/new_point/photos_upload'))); ?>', 'error');
                        return false;
                    }
                    if (count_current_photos > max_photos_per_point) {
                        my_get_message('<?php echo(my_pass_through(@self::trace('errors/new_point/photos_upload_more_than_available'))); ?>' + (count_current_photos - max_photos_per_point), 'error');
                        return false;
                    }
                    return true;
                }

                if (!photos_input.val()) {
                    my_get_message('<?php echo(my_pass_through(@self::trace('errors/new_point/photos_upload'))); ?>', 'error');
                    return false;
                } else if (!x_input.val() || !y_input.val()) {
                    my_get_message('<?php echo(my_pass_through(@self::trace('errors/new_point/coords_empty'))); ?>', 'error');
                    return false;
                }
                return true;
            };

            // Сброс
            var reset_form = function () {
                my_fileuploader_object.reset();
                $('#' + form_id + ' input:not(#add_new_point_form_email)').val('');
                $('#' + form_id + ' textarea').val('');
            };

            // Все ли фото загружены
            var is_all_images_uploaded = function () {

                var all_uploaded = true;
                $.each(images_for_uploading, function (id, value) {
                    if (typeof (value) !== 'undefined') {
                        all_uploaded = false;
                        return false;
                    }
                });
                // Если все фото загружены, то очищаем массив
                if (all_uploaded === true) {
                    images_for_uploading = new Object();
                }
                return all_uploaded;
            };

            // Удаление метки
            var delete_point = function () {
                var form_data = form.serializeArray();
                $.ajax({
                    type: "POST",
                    url: 'map/ajax_delete_point?<?php echo(self::get_query_string()); ?>',
                    data: form_data,
                    success: function (data) {
                        try {
                            var result = JSON.parse(data);
                            if (result['status'] === '<?php echo(MY_SUCCESS_CODE); ?>') {
                                my_get_message(result['message'], 'success');
                                my_map_vendor.delete_placemark(result['data']['id']);
                                my_map_vendor.close_add_new_point();
                            }
                        } catch (error) {
                            my_get_message('<?php echo(my_pass_through(@self::trace('errors/system'))); ?>', 'error');
                        }

                    },
                    error: function (jqXHR) {
                        my_get_message(jqXHR.responseText, 'error');
                    }

                }).always(function () {

                });
            }

            var interface = {
                init: function () {
                    form.submit(function (event) {
                        event.preventDefault();
                        my_new_point.submit();
                    });

                    $('button#delete_placemark').click(function (event) {

                        event.preventDefault();

                        swal({
                            title: "<?php echo(my_pass_through(@self::trace('sweet_alert/are_you_sure'))); ?>",
                            text: "<?php echo(my_pass_through(@self::trace('sweet_alert/delete_placemark/notation'))); ?>",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#964271",
                            confirmButtonText: "<?php echo(my_pass_through(@self::trace('sweet_alert/yes'))); ?>",
                            cancelButtonText: "<?php echo(my_pass_through(@self::trace('sweet_alert/no'))); ?>",
                            closeOnConfirm: true
                        },
                        function () {
                                delete_point();
                        });
                    });
                },
                
                // Убираем настройки для обновления записи
                reset_updater: function () {
                    $('#update_placemark_field').html('');
                    $('#new_placemark_email').show();
                    $('#update_placemark_password').hide();
                    $('button#delete_placemark').hide();

                    $('#add_new_point .header_1').html('<?php echo(my_pass_through(@self::trace('text/new_point/title'))); ?>');
                },
                
                // Подготовка данных для обновления
                prepare_update: function (id, data) {
                    $('#add_new_point_form_title').val(data.title);
                    $('#add_new_point_form_comment').val(data.comment);
                    var existing_photos = '<div class="label_1"><?php echo(my_pass_through(@self::trace('buttons/new_point/existing_photos_title'))); ?></div>';
                    $.each(data.photos, function (index, value) {
                        existing_photos = existing_photos + '<img src="' + value.dir + '8_' + value.name + '">\n\
<div><input type="checkbox" id="delete_photo_' + value.name + '" name="' + form_name + '[delete_photos][' + value.name + ']" value="1" class="delete_existing_photo_checkbox"/>\n\
<label for="delete_photo_' + value.name + '"><span></span><?php echo(my_pass_through(@self::trace('buttons/new_point/delete_photo'))); ?></label>\n\
</div>';
                    });
                    var inputs = '<input type="hidden" name="' + form_name + '[id]" value="' + id + '">';
                    $('#update_placemark_field').html(existing_photos + "\n" + inputs);
                    $('#new_placemark_email').hide();
                    $('#update_placemark_password').show();
                    $('button#delete_placemark').show();
                    $('#add_new_point .header_1').html('<?php echo(my_pass_through(@self::trace('text/new_point/title_update'))); ?>');
                },
                
                // Добавление фото
                add_photos: function (photo) {
                    var new_photos = photos_input.val() + photo + ' ';
                    photos_input.val(new_photos);
                },
                
                // Удаление фото
                delete_photos: function (photo) {
                    var old_photos = photos_input.val();
                    var new_photos = old_photos.replace(photo + ' ', '');
                    photos_input.val(new_photos);
                },
                
                // Сброс формы
                reset: function () {
                    reset_form();
                },
                
                // Добавление фото в очередь загрузки
                add_uploaded_image_to_queue: function (img) {
                    $.each(img, function (id, value) {
                        images_for_uploading[value] = true;
                    });
                },
                
                // Убрать фото из очереди загрузки, при загрузке на сервер
                clear_uploaded_image_from_queue: function (img) {
                    $.each(img, function (id, value) {
                        images_for_uploading[value] = undefined;
                    });
                },
                
                // Проверка - все ли фото загружены
                is_all_images_uploaded: function () {
                    return is_all_images_uploaded();
                },
                
                // Удалить метку
                delete_point: function () {
                    delete_point();
                },
                
                // Подтвердить запрос добавления/редактирования метки
                submit: function () {

                    if (check()) {
                        send_status = true;
                        var form_data = form.serializeArray();
                        my_get_message('<?php echo(self::trace('text/new_point/loading')); ?>', 'notice', false);                        
                        $.ajax({
                            type: "POST",
                            url: form.attr('action'),
                            data: form_data,
                            success: function (data) {
                                try {
                                    var result = JSON.parse(data);
                                    if (result['status'] === '<?php echo(MY_SUCCESS_CODE); ?>') {
                                        my_get_message(result['message'], 'success');
                                        my_map_vendor.init_target_placemark(result['data']['id']);

                                        if (typeof (result['data']['email']) !== 'undefined') {
                                            $('#add_new_point_form_email').val(result['data']['email']);
                                        }
                                    }
                                } catch (error) {
                                    my_get_message('<?php echo(my_pass_through(@self::trace('errors/system'))); ?>', 'error');
                                }

                            },
                            error: function (jqXHR) {
                                my_get_message(jqXHR.responseText, 'error');
                            }

                        }).always(function () {
                            $("#add_new_point .loading").hide();
                            send_status = false;
                        });
                    }
                }
            }

            return interface;
        })();
        
        // Можно обратиться к объекту двумя способами - через явно указанное имя или через генератор имени (применяется в моделях и т.д.)
        my_new_point = my_<?php echo($form_new_point_model::FORM_NAME); ?>_object;
        my_new_point.init();
    });
</script>