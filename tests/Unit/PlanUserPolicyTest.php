<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\User;
use App\Models\PlanUser;
use App\Policies\PlanUserPolicy;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class PlanUserPolicyTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
    }

    public function test_user_can_start_own_unstarted_plan(): void
    {
        $user = new User();
        $user->id = 1;
        $planUser = new PlanUser([
            'user_id' => 1,
            'started_at' => null,
        ]);

        $policy = new PlanUserPolicy();


        $this->assertTrue($policy->start($user, $planUser));
    }

    public function test_user_cannot_start_other_users_plan(): void
    {
        $user = new User(['id' => 1]);
        $planUser = new PlanUser([
            'user_id' => 2,
            'started_at' => null,
        ]);

        $policy = new PlanUserPolicy();

        $this->assertFalse($policy->start($user, $planUser));
    }

    public function test_user_cannot_start_already_started_plan(): void
    {

        $user = new User(['id' => 1]);

        $planUser = new PlanUser([
            'user_id' => 1,
            'started_at' => now(),
        ]);

        $policy = new PlanUserPolicy();

        $this->assertFalse($policy->start($user, $planUser));
    }

    public function test_user_can_view_own_plan(): void
    {
        $user = new User(['id' => 1]);
        $planUser = new PlanUser(['user_id' => 1]);

        $policy = new PlanUserPolicy();

        $this->assertTrue($policy->view($user, $planUser));
    }

    public function test_trainer_can_view_clients_plan(): void
    {
        $user = new User(['id' => 1, 'role' => 'trainer']);
        $plan = new Plan(['trainer_id' => 1]);

        $planUser = new PlanUser(['user_id' => 2]);
        $planUser->setRelation('plan', $plan);

        $policy = new PlanUserPolicy();

        $this->assertTrue($policy->view($user, $planUser));
    }

    public function test_unrelated_user_cannot_view_plan(): void
    {
        $user = new User(['id' => 99, 'role' => 'client']);
        $plan = new Plan(['trainer_id' => 1]);

        $planUser = new PlanUser(['user_id' => 2]);
        $planUser->setRelation('plan', $plan);

        $policy = new PlanUserPolicy();

        $this->assertFalse($policy->view($user, $planUser));
    }
}
