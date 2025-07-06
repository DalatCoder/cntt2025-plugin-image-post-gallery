jQuery(document).ready(function ($) {
  let imageIndex = $(".gallery-item").length;

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
      .replace(/{{thumbnail}}/g, thumbnail);

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
      });
    }
  });

  // Update no images message
  function updateNoImagesMessage() {
    const hasImages = $(".gallery-item").length > 0;
    $("#no-images-message").toggle(!hasImages);
  }

  // Reindex gallery items after removal
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

  // Make gallery items sortable
  if (typeof $.fn.sortable !== "undefined") {
    $("#gallery-images-container").sortable({
      items: ".gallery-item",
      cursor: "move",
      opacity: 0.7,
      placeholder: "gallery-item-placeholder",
      stop: function () {
        reindexGalleryItems();
      },
    });
  }

  // Initialize no images message state
  updateNoImagesMessage();
});
