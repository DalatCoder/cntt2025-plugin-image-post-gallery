jQuery(document).ready(function ($) {
  let currentGalleryImages = [];
  let currentImageIndex = 0;

  // Handle gallery item clicks
  $(document).on("click", ".cntt2025-gallery-item", function (e) {
    e.preventDefault();

    const galleryContainer = $(this).closest(".cntt2025-gallery-container");
    const galleryItems = galleryContainer.find(".cntt2025-gallery-item");

    // Build array of images in this gallery
    currentGalleryImages = [];
    galleryItems.each(function (index) {
      currentGalleryImages.push({
        url: $(this).data("image-url"),
        caption: $(this).data("image-caption") || "",
        index: index,
      });
    });

    // Get clicked image index
    currentImageIndex = $(this).data("image-index");

    // Show modal with clicked image
    showModal(currentImageIndex);
  });

  // Show modal function
  function showModal(imageIndex) {
    if (!currentGalleryImages[imageIndex]) return;

    const image = currentGalleryImages[imageIndex];

    // Update modal content
    $("#gallery-modal-image").attr("src", image.url).attr("alt", image.caption);
    $("#gallery-modal-caption").text(image.caption);

    // Show/hide navigation arrows
    $("#gallery-prev").toggle(currentGalleryImages.length > 1);
    $("#gallery-next").toggle(currentGalleryImages.length > 1);

    // Show modal
    $("#cntt2025-gallery-modal").addClass("show");
    $("body").addClass("modal-open").css("overflow", "hidden");

    // Update current index
    currentImageIndex = imageIndex;
  }

  // Close modal
  function closeModal() {
    $("#cntt2025-gallery-modal").removeClass("show");
    $("body").removeClass("modal-open").css("overflow", "");
    currentGalleryImages = [];
    currentImageIndex = 0;
  }

  // Modal close button
  $("#gallery-modal-close").on("click", function (e) {
    e.preventDefault();
    closeModal();
  });

  // Click outside modal to close
  $("#cntt2025-gallery-modal").on("click", function (e) {
    if (e.target === this) {
      closeModal();
    }
  });

  // Previous image
  $("#gallery-prev").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (currentGalleryImages.length > 1) {
      currentImageIndex =
        currentImageIndex === 0
          ? currentGalleryImages.length - 1
          : currentImageIndex - 1;
      showModal(currentImageIndex);
    }
  });

  // Next image
  $("#gallery-next").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (currentGalleryImages.length > 1) {
      currentImageIndex =
        currentImageIndex === currentGalleryImages.length - 1
          ? 0
          : currentImageIndex + 1;
      showModal(currentImageIndex);
    }
  });

  // Keyboard navigation
  $(document).on("keydown", function (e) {
    if (!$("#cntt2025-gallery-modal").hasClass("show")) return;

    switch (e.keyCode) {
      case 27: // Escape
        closeModal();
        break;
      case 37: // Left arrow
        $("#gallery-prev").click();
        break;
      case 39: // Right arrow
        $("#gallery-next").click();
        break;
    }
  });

  // Prevent modal image clicks from closing modal
  $("#gallery-modal-image, #gallery-modal-caption").on("click", function (e) {
    e.stopPropagation();
  });

  // Touch/swipe support for mobile
  let touchStartX = null;
  let touchStartY = null;

  $("#cntt2025-gallery-modal").on("touchstart", function (e) {
    const touch = e.originalEvent.touches[0];
    touchStartX = touch.clientX;
    touchStartY = touch.clientY;
  });

  $("#cntt2025-gallery-modal").on("touchend", function (e) {
    if (!touchStartX || !touchStartY) return;

    const touch = e.originalEvent.changedTouches[0];
    const diffX = touchStartX - touch.clientX;
    const diffY = touchStartY - touch.clientY;

    // Check if horizontal swipe is more significant than vertical
    if (Math.abs(diffX) > Math.abs(diffY)) {
      if (Math.abs(diffX) > 50) {
        // Minimum swipe distance
        if (diffX > 0) {
          // Swipe left - next image
          $("#gallery-next").click();
        } else {
          // Swipe right - previous image
          $("#gallery-prev").click();
        }
      }
    }

    touchStartX = null;
    touchStartY = null;
  });

  // Image loading error handling
  $("#gallery-modal-image").on("error", function () {
    $(this).attr(
      "src",
      "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjBmMGYwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkzhu5dpIGtow7RuZyB0aOG7gyB0YWkgaMOsbmggYW5oPC90ZXh0Pjwvc3ZnPg=="
    );
    $("#gallery-modal-caption").text("Không thể tải hình ảnh");
  });

  // Preload adjacent images for smoother navigation
  function preloadAdjacentImages() {
    if (currentGalleryImages.length <= 1) return;

    const prevIndex =
      currentImageIndex === 0
        ? currentGalleryImages.length - 1
        : currentImageIndex - 1;
    const nextIndex =
      currentImageIndex === currentGalleryImages.length - 1
        ? 0
        : currentImageIndex + 1;

    // Preload previous image
    if (currentGalleryImages[prevIndex]) {
      const prevImg = new Image();
      prevImg.src = currentGalleryImages[prevIndex].url;
    }

    // Preload next image
    if (currentGalleryImages[nextIndex]) {
      const nextImg = new Image();
      nextImg.src = currentGalleryImages[nextIndex].url;
    }
  }

  // Call preload when modal is shown
  $("#cntt2025-gallery-modal").on("transitionend", function () {
    if ($(this).hasClass("show")) {
      preloadAdjacentImages();
    }
  });
});
