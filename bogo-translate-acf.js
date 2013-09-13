jQuery(function($){
    // change the choosen template for this page
    // trigger change on the select to make acf render correct fields for the template
    $( '#page_template' ).val( bogo_acf_js.parents_default_template ).trigger( 'change' );
});