/**
 * Securepass Options.
 *
 * @class           WPSecurePassOptions
 * @author          =undo= <g.@wpxtre.me>
 * @copyright       Copyright (C) 2014 wpXtreme Inc. All Rights Reserved.
 * @date            2014-10-15
 * @version         1.0.0
 */

jQuery( function( $ )
{
  "use strict";
  window.WPSecurePassOptions = (function()
  {
    /**
     * This object
     *
     * @type {{version: string, init: _init}}
     * @private
     */
    var _WPSecurePassOptions = {
      version: '1.0.0',
      init: _init
    };

    /**
     * Init
     *
     * @returns {{version: string, init: _init}}
     * @private
     */
    function _init()
    {
      // Init the select combo protocol.
      _initSelectProtocol();

      // Init selects for uses
      _initSelectsUsers();

      return _WPSecurePassOptions;

    }

    /**
     * Init the selects for users.
     *
     * @private
     */
    function _initSelectsUsers()
    {
      var $source = $( 'select#temp-users' );
      var $destination = $( 'select#wpsp_field_list_users' );
      var $button_add = $( 'button#wpsp-button-add' );
      var $button_remove = $( 'button#wpsp-button-remove' );
      var $button_remove_all = $( 'button#wpsp-button-remove-all' );

      // Button add
      $button_add.on( 'click', function( e )
      {
        e.preventDefault();

        var value = $( 'option:selected', $source ).val();
        var text = $( 'option:selected', $source ).text();

        $destination.append( '<option value="' + value + '">' + text + '</option>' );

        return false;
      } );

      // Button remove
      $button_remove.on( 'click', function( e )
      {
        e.preventDefault();

        $( 'option:selected', $destination ).remove();

        return false;
      } );

      // Button remove all
      $button_remove_all.on( 'click', function( e )
      {
        e.preventDefault();

        // Select all option item.
        $destination.find( 'option' ).attr( 'selected', 'selected' );
        $button_remove.trigger( 'click' );

        return false;
      } );

      // This is a hack to send in POST/GET the new value in the multiple select tag
      $( 'form#wp-securepass-options' ).submit( function()
      {
        // Select all option item.
        $destination.find( 'option' ).attr( 'selected', 'selected' );
      } );
    }

    /**
     * Init the select combo protocol.
     *
     * @private
     */
    function _initSelectProtocol()
    {
      $( 'form#wp-securepass-options select#wpsp_field_protocol' ).on( 'change', function()
      {

        switch( $( this ).val() ) {

          // RADIUS
          case 'radius':
            $( 'form#wp-securepass-options h3:nth-child(10), form#wp-securepass-options table:nth-child(11)' ).hide();
            $( 'form#wp-securepass-options h3:nth-child(8), form#wp-securepass-options table:nth-child(9)' ).show();
            break;

          // RESTFul
          case 'restful':
            $( 'form#wp-securepass-options h3:nth-child(10), form#wp-securepass-options table:nth-child(11)' ).show();
            $( 'form#wp-securepass-options h3:nth-child(8), form#wp-securepass-options table:nth-child(9)' ).hide();
            break;
        }

      } );
    }

    return _init();
  })();

} );