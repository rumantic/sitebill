<link rel="stylesheet" href="{{$estate_folder}}/apps/gallery/resources/plugins/fotorama/fotorama.css"/>
<script src="{{$estate_folder}}/apps/gallery/resources/plugins/fotorama/fotorama.js"></script>
@include('gallery')
<script>
    $(document).ready(function () {
        if($('.photoslider').length > 0){
            $('.photoslider').each(function(){
                $(this).on('fotorama:fullscreenenter fotorama:fullscreenexit', function (e, fotorama) {
                    if (e.type === 'fotorama:fullscreenenter') {
                        fotorama.setOptions({fit: 'contain'});
                    } else {
                        fotorama.setOptions({fit: 'cover'});
                    }
                }).fotorama({
                    nav: "thumbs",
                    allowfullscreen: true,
                    width: "100%",
                    ratio: "1500/500",
                    fit: "cover"
                });
            });

        }
    });

</script>

