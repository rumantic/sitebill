@if(isset($image) && is_array($image) && count($image) > 0)
    <div class="gallery-slider">
        <div id="{{$galleryid}}" class="photoslider">
            @foreach($image as $photo)
            <img src="{{$sitebill->createMediaIncPath($photo)}}">
            @endforeach
        </div>
    </div>
@endif
