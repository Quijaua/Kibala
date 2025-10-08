
// lightGallery

lightGallery(document.querySelector('.lightgallery'), {
  plugins: [lgFullscreen, lgZoom]
});

lightGallery(document.querySelector('.lightgallery-carrossel'), {
  plugins: [lgFullscreen, lgZoom, lgThumbnail],
  selector: '.gallery-item'
});