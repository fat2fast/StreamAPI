<?php

use yii\db\Migration;

/**
 * Adds unique active-session guard per streamer.
 */
class m260311_101500_add_active_streamer_unique_constraint extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->driverName !== 'mysql') {
            return;
        }

        $this->addColumn(
            '{{%livestreams}}',
            'active_streamer_id',
            $this->integer()->unsigned()->null()
        );

        $this->execute(
            "UPDATE {{%livestreams}} SET active_streamer_id = CASE WHEN `status` = 'active' THEN `streamer_id` ELSE NULL END"
        );

        $this->createIndex(
            '{{%uq-livestreams-active-streamer-id}}',
            '{{%livestreams}}',
            'active_streamer_id',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ($this->db->driverName !== 'mysql') {
            return;
        }

        $this->dropIndex('{{%uq-livestreams-active-streamer-id}}', '{{%livestreams}}');
        $this->dropColumn('{{%livestreams}}', 'active_streamer_id');
    }
}
