<?php

namespace Junges\InviteCodes\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Junges\InviteCodes\Events\DeletedExpiredInvitesEvent;
use Junges\InviteCodes\Http\Models\Invite;

class DeleteExpiredInvitesCommand extends Command
{
    protected $signature = 'invite-codes:clear';

    protected $description = 'Delete all expired invites from your database';

    public function handle(): int
    {
        $model = app(config('invite-codes.models.invite_model', Invite::class));
        $model->where('expires_at', '<=', Carbon::now(config('app.timezone')))->delete();

        $this->info('Delete all expired invites.');

        event(new DeletedExpiredInvitesEvent());

        return 0;
    }
}
