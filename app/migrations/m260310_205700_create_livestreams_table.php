<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%livestreams}}`.
 */
class m260310_205700_create_livestreams_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%livestreams}}', [
            'id' => $this->primaryKey()->unsigned(),
            'streamer_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string(255)->notNull(),
            'status' => "ENUM('active','closed') NOT NULL DEFAULT 'active'",
            'started_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'closed_at' => $this->timestamp()->null()->defaultValue(null),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            '{{%idx-livestreams-streamer_id}}',
            '{{%livestreams}}',
            'streamer_id'
        );

        $this->createIndex(
            '{{%idx-livestreams-status}}',
            '{{%livestreams}}',
            'status'
        );

        $this->addForeignKey(
            '{{%fk-livestreams-streamer_id}}',
            '{{%livestreams}}',
            'streamer_id',
            '{{%users}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-livestreams-streamer_id}}', '{{%livestreams}}');
        $this->dropIndex('{{%idx-livestreams-status}}', '{{%livestreams}}');
        $this->dropIndex('{{%idx-livestreams-streamer_id}}', '{{%livestreams}}');
        $this->dropTable('{{%livestreams}}');
    }
}
