(function ($, Drupal, once) {
  Drupal.behaviors.registerEvent = {
    attach: function (context, settings) {
      once("register-event", $(".use-ajax", context)).forEach(function (
        element
      ) {
        $(element).on("click", function (event) {
          event.preventDefault();

          $.ajax({
            url: $(this).attr("href"),
            type: "GET",
            success: function (response) {
              var messageContainer = $("#register-message");

              if (response.status === "success") {
                messageContainer.html(
                  '<div class="messages messages--status">' +
                    response.message +
                    "</div>"
                );
              } else if (response.status === "warning") {
                messageContainer.html(
                  '<div class="messages messages--warning">' +
                    response.message +
                    "</div>"
                );
              } else if (response.status === "error") {
                messageContainer.html(
                  '<div class="messages messages--error">' +
                    response.message +
                    "</div>"
                );
              }
            },
          });
        });
      });
    },
  };
})(jQuery, Drupal, once);
