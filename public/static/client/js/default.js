var renderTemplate = function(templateSelector, params) {
    return Handlebars.compile($(templateSelector).html())(params);
}

;(function($) {

    $(function() {
        $('.ui.checkbox').checkbox();

        $(".drop-area").dmUploader({
            url              : '/slideshow/upload',
            dataType         : 'json',
            fileName         : 'file',
            allowedTypes     : 'image/*',
            extraData        : {
                'csrf_token': $("meta[name=csrf-token]").attr('content')
            },
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
                    $("#preview-file-" + id).find("img").remove();
                }
            },
            onUploadProgress : function(id, percent) {
                var progressBar = $("#preview-file-" + id).find('.progress')

                progressBar.progress({ percent: percent });
                progressBar.find('.label').text(percent + "%");
            },
            onUploadSuccess  : function(id, data) {
            },
            onUploadError    : function(id, message) {
                var progressBar = $("#preview-file-" + id).find('.progress')

                progressBar.progress({ percent: 0 });
                progressBar.find('.label').text("Error!");
            }
        });

        $(".drop-area")
            .on("dragover", function(e) {
                $(this).addClass('warning message');
            })
            .on('dragleave', function(e) {
                $(this).removeClass('warning message');
            });

        if ($(".slideshows").length > 0) {
            var checkSlideShowStatus = function() {
                $.getJSON('/slideshow/status', function(slideshows) {
                    slideshows.forEach(function(slideshow) {
                        var slideshowRow = $(".slideshow-" + slideshow.uuid);

                        slideshowRow.find(".status").text(slideshow.status);

                        if (slideshow.status.toLowerCase() == "finish") {
                            slideshowRow.find(".action a:first").removeClass('disabled');
                        }else{
                            slideshowRow.find(".action a:first").addClass('disabled');
                        }
                    });
                });
            };
            checkSlideShowStatus();

            setInterval(checkSlideShowStatus, 5000);
        }
    });

})(jQuery);
