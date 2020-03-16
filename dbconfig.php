<?php

if( !class_exists( 'CAH_DBHelper' ) ) {
    class CAH_DBHelper
    {
        private $db_server, $db_user, $db_pass, $db, $db_charset, $db_connection;

        public function __construct() {

            $this->db_server = 'net1251.net.ucf.edu';
            $this->db_user = 'communication';
            $this->db_pass = 'kSIF8fp2NLwfac8g';
            $this->db = 'cah';
            $this->db_charset = 'utf-8';

            $this->db_connection = false;

        }

        public function get_db() : ?mysqli {

            if( $this->db_connection === false ) {
                $this->db_connection = mysqli_connect( $this->db_server, $this->db_user, $this->db_pass, $this->db );
                mysqli_set_charset( $this->db_connection, $this->db_charset);
            }
            return $this->db_connection;

        }

        public function close_db() {

            if( $this->db_connection instanceof mysqli ) {
                mysqli_close( $this->db_connection );
            }

            $this->db_connection = false;

        }

        public function query( string $sql ) : ?mysqli_result {
            
            if( empty( $sql ) ) return null;

            $result = mysqli_query( $this->get_db(), $sql );

            if( $result instanceof mysqli_result && $result->num_rows > 0 ) {
                return $result;
            }
            return null;
        }
    }
}
?>