<?php

class Migration_add_marca_modelo_serie_to_os extends CI_Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE `os`
            ADD `marca` VARCHAR(80) NULL,
            ADD `modelo` VARCHAR(80) NULL,
            ADD `numero_serie` VARCHAR(80) NULL;');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `os` DROP `marca`, DROP `modelo`, DROP `numero_serie`;');
    }
}
