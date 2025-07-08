jQuery(document).ready(function ($) {
  let imageIndex = $(".gallery-item").length;

  // Initialize sortable functionality
  initializeSortable();

  // Open WordPress Media Library
  $("#add-gallery-image").on("click", function (e) {
    e.preventDefault();

    const mediaUploader = wp.media({
      title: "Chọn hình ảnh cho Gallery",
      button: {
        text: "Thêm vào Gallery",
      },
      multiple: true,
    });

    mediaUploader.on("select", function () {
      const attachments = mediaUploader.state().get("selection").toJSON();

      attachments.forEach(function (attachment) {
        addImageToGallery(attachment);
      });

      updateNoImagesMessage();
      updateOrderNumbers();
    });

    mediaUploader.open();
  });

  // Add image to gallery function
  function addImageToGallery(attachment) {
    const template = $("#gallery-item-template").html();
    const thumbnail =
      attachment.sizes && attachment.sizes.medium
        ? attachment.sizes.medium.url
        : attachment.url;

    const newItem = template
      .replace(/{{index}}/g, imageIndex)
      .replace(/{{id}}/g, attachment.id)
      .replace(/{{url}}/g, attachment.url)
      .replace(/{{thumbnail}}/g, thumbnail)
      .replace(/{{orderNumber}}/g, imageIndex + 1);

    $("#gallery-images-container").append(newItem);
    imageIndex++;
  }

  // Remove image
  $(document).on("click", ".remove-image", function (e) {
    e.preventDefault();

    if (confirm("Bạn có chắc muốn xóa hình ảnh này?")) {
      $(this)
        .closest(".gallery-item")
        .fadeOut(300, function () {
          $(this).remove();
          updateNoImagesMessage();
          reindexGalleryItems();
          updateOrderNumbers();
        });
    }
  });

  // Clear all images
  $("#clear-all-images").on("click", function (e) {
    e.preventDefault();

    if (confirm("Bạn có chắc muốn xóa tất cả hình ảnh?")) {
      $(".gallery-item").fadeOut(300, function () {
        $(this).remove();
        imageIndex = 0;
        updateNoImagesMessage();
        updateOrderNumbers();
      });
    }
  });

  // Update no images message
  function updateNoImagesMessage() {
    const hasImages = $(".gallery-item").length > 0;
    $("#no-images-message").toggle(!hasImages);

    // Show/hide sort info
    $(".sort-info").toggle(hasImages);
  }

  // Update order numbers
  function updateOrderNumbers() {
    $(".gallery-item").each(function (index) {
      $(this)
        .find(".order-number")
        .text(index + 1);
    });
  }

  // Reindex gallery items after removal or reordering
  function reindexGalleryItems() {
    $(".gallery-item").each(function (index) {
      $(this).attr("data-index", index);
      $(this)
        .find('input[name*="gallery_images"]')
        .each(function () {
          const name = $(this).attr("name");
          const newName = name.replace(/\[\d+\]/, "[" + index + "]");
          $(this).attr("name", newName);
        });
    });
    imageIndex = $(".gallery-item").length;
  }

  // Initialize sortable functionality
  function initializeSortable() {
    $("#gallery-images-container").sortable({
      items: ".gallery-item",
      handle: ".drag-handle",
      cursor: "move",
      opacity: 0.8,
      placeholder: "gallery-item-placeholder",
      tolerance: "pointer",
      helper: "clone",
      revert: 150,
      start: function (e, ui) {
        ui.placeholder.height(ui.item.height());
        ui.item.addClass("ui-sortable-helper");
      },
      stop: function (e, ui) {
        ui.item.removeClass("ui-sortable-helper");
        reindexGalleryItems();
        updateOrderNumbers();

        // Show success feedback
        showSortFeedback();
      },
    });
  }

  // Show feedback when sorting is complete
  function showSortFeedback() {
    const $feedback = $(
      '<div class="sort-feedback">✓ Đã cập nhật thứ tự</div>'
    );
    $feedback.css({
      position: "fixed",
      top: "50px",
      right: "20px",
      background: "#4CAF50",
      color: "white",
      padding: "10px 15px",
      borderRadius: "5px",
      zIndex: 10000,
      boxShadow: "0 2px 8px rgba(0,0,0,0.2)",
    });

    $("body").append($feedback);

    setTimeout(function () {
      $feedback.fadeOut(300, function () {
        $feedback.remove();
      });
    }, 2000);
  }

  // Initialize state
  updateNoImagesMessage();
  updateOrderNumbers();
});
