var renderTemplate = function(templateSelector, params) {
    return Handlebars.compile($(templateSelector).html())(params);
}

;(function($) {

    $(function() {
        $('.ui.checkbox').checkbox();

        $(".drop-area").dmUploader({
            url              : '/upload',
            dataType         : 'json',
            allowedTypes     : 'image/*',
            onNewFile        : function(id, file) {
                if (id === 0) {
                    $(".file-area > .upload-first").hide();
                    $(".file-area > .files").show();
                }

                $(".file-area > .files").append(renderTemplate("#preview-file", {
                    "id"  : id,
                    "file": file
                }));

                if (typeof FileReader !== "undefined") {
                    var reader = new FileReader();
                    var img = $("#preview-file-" + id).find("img").eq(0);

                    reader.onload = function (e) {
                        img.attr('src', e.target.result);
                    }

                    reader.readAsDataURL(file);
                }else{
                    $('#demo-files').find('.demo-image-preview').remove();
                }
            },
            onUploadProgress : function() {

            },
            onUploadSuccess  : function(id, data) {

            },
        });

        $(".drop-area")
            .on("dragover", function(e) {
                $(this).addClass('warning message');
            })
            .on('dragleave', function(e) {
                $(this).removeClass('warning message');
            });
    });

})(jQuery);
