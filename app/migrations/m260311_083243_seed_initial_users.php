<?php

use yii\db\Migration;

class m260311_083243_seed_initial_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

        $this->batchInsert('{{%users}}', ['id', 'username', 'email', 'role', 'created_at', 'updated_at'], [
            [1, 'streamer_test', 'streamer@test.local', 'streamer', $now, $now],
            [2, 'audience_test', 'audience@test.local', 'audience', $now, $now],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%users}}', ['email' => 'streamer@test.local']);
        $this->delete('{{%users}}', ['email' => 'audience@test.local']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260311_083243_seed_initial_users cannot be reverted.\n";

        return false;
    }
    */
}
