jQuery(document).ready(function($) {
  var data = { 
    action: 'render_cart66_account_widget',
    logged_in_message:  cm_account_widget.logged_in_message,
    logged_out_message: cm_account_widget.logged_out_message,
    show_link_history:  cm_account_widget.show_link_history,
    show_link_profile:  cm_account_widget.show_link_profile
  };

  $.post(cm_account_widget.ajax_url, data, function(response) {
    $('.cm-account-widget').html(response);
  });

  $('.cm-account-widget').spin('small');
});

