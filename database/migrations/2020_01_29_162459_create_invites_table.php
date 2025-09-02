<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table_name = config('invite-codes.tables.invites_table', 'invites');

        Schema::create($table_name, static function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('max_usages')->nullable();
            $table->string('to')->nullable();
            $table->integer('uses')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('invite-codes.tables.invites_table', 'invites'));
    }
};
