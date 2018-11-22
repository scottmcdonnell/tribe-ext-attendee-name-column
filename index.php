<?php
/**
* Plugin Name:     Event Tickets Extension: Event Tickets Attendee Column
* Description:     Adds a Attendee name column to the Attendees list in Admin
* Version:         1.0.0
* Extension Class: Tribe__Extension__Event_Tickets_Attendee_Column
* Author:          Scott McDonnell
* Author URI:      http://www.jammedia.com
* License:         GPLv2 or later
* License URI:     https://www.gnu.org/licenses/gpl-2.0.html
*/

// Do not load unless Tribe Common is fully loaded.
if ( ! class_exists( 'Tribe__Extension' ) ) {
   return;
}

/**
* Extension main class, class begins loading on init() function.
*/
class Tribe__Extension__Event_Tickets_Attendee_Column extends Tribe__Extension {

   /**
    * Setup the Extension's properties.
    *
    * This always executes even if the required plugins are not present.
    */
   public function construct() {
      $this->add_required_plugin( 'Tribe__Tickets__Main', '4.3' );
      $this->add_required_plugin( 'Tribe__Tickets_Plus__Main', '4.3' ); //dont think we need the tickets plus to work
   }

   /**
    * Extension initialization and hooks.
    */
   public function init() {
  
      add_filter( 'tribe_tickets_event_attendees', array($this, 'custom_tribe_tickets_event_attendees'), 10, 2);
      add_filter( 'tribe_tickets_attendee_table_columns', array($this, 'custom_tribe_tickets_attendee_table_columns'), 20);
      add_filter( 'tribe_events_tickets_attendees_table_column', array($this, 'custom_populate_attendee_name_column'), 10, 3 );
      add_filter( 'tribe_tickets_search_attendees_by', array($this, 'custom_tribe_tickets_search_attendees_by'), 10, 3);
   }

    /**
    * set an extra field called 'attendee_name' from the meta fields and adds it to each attendee.
    */
    public function custom_tribe_tickets_event_attendees($attendees, $post_id) {
      foreach ( $attendees as &$attendee ) {
        $attendee['attendee_name'] = $this->get_attendee_name($attendee);
      }
      return $attendees;
    }

    /**
    * Set the attendee_name from your field set meta. You can customise to match the meta you are gathering.
    */
    public function get_attendee_name($attendee_data) {
      $meta = get_post_meta( $attendee_data['attendee_id'], Tribe__Tickets_Plus__Meta::META_KEY, true );
      $name = "-";
      if ($meta) {
        if (isset($meta['first-name'])) $name = $meta['first-name'];
        if (isset($meta['second-name'])) $name .= ' ' . $meta['second-name'];
        if (isset($meta['last-name'])) $name .= ' ' . $meta['last-name'];
        if (isset($meta['surname'])) $name .= ' ' . $meta['surname'];
        if (isset($meta['full-name'])) $name = $meta['full-name'];

        if (!empty($name)) $attendee_data['attendee_name'] = $name;
      }
      return $name;
    }

    /**
    * Add an "Attendee" column to the attendee list
    */
    public function custom_tribe_tickets_attendee_table_columns( $columns) {
        return Tribe__Main::array_insert_after_key( 'ticket', $columns, array(
          'attendee_name' => esc_html_x( 'Attendee', 'attendee table meta', 'event-tickets' ),
       ));
    }

    /**
    * Populate the "Attendee" column with the 'attendee_name' values set in custom_tribe_tickets_attendee_data()
    */
    public function custom_populate_attendee_name_column( $value, array $item, $column ) {
        if ( 'attendee_name' !== $column || empty($item['attendee_id']))   return $value;
        return  empty($item['attendee_name']) ? "-" : $item['attendee_name'];
    }

    /**
    * Add to the search keys so that the attendee_name is searchable in the admin list
    */
    public function custom_tribe_tickets_search_attendees_by($search_keys, $items, $search ) {
      $search_keys[] = 'attendee_name';
      return  $search_keys;
    }
}