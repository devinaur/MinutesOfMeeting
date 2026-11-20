<?php

namespace Tests\Feature;

use App\Livewire\MoM\MoMGenerator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MoMGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'devina@yusen.co.id'],
            [
                'name' => 'Devina Rahma',
                'username' => 'devina',
                'department' => 'IT',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk ke MoM Assistant');
    }

    public function test_user_can_authenticate_via_login_form(): void
    {
        Livewire::test('pages.auth.login')
            ->set('form.email', 'devina')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('home', absolute: false));

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_authenticated_user_can_access_mom_assistant_page(): void
    {
        $response = $this->actingAs($this->user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Minutes of Meeting Generator');
        $response->assertSee('Devina Rahma');
    }

    public function test_livewire_can_add_attendee_and_agenda(): void
    {
        Livewire::actingAs($this->user)
            ->test(MoMGenerator::class)
            ->call('addAttendee')
            ->assertCount('attendees', 1)
            ->call('addAgendaItem')
            ->assertCount('agendaItems', 1);
    }

    public function test_livewire_cascading_template_dropdown_behavior(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(MoMGenerator::class)
            ->assertSet('department', 'business_unit')
            ->assertSet('templateVariant', 'standard')
            ->assertDontSee('Format:');

        $component->set('department', 'ssm')
            ->assertSet('templateVariant', 'weekly')
            ->assertSee('Format:')
            ->assertSee('Weekly Progress (Word)')
            ->assertSee('Project Review (Excel)');

        $component->set('templateVariant', 'project');
        $this->assertEquals('xlsx', $component->get('currentFormat'));

        $component->set('templateVariant', 'weekly');
        $this->assertEquals('docx', $component->get('currentFormat'));

        $component->set('department', 'it')
            ->assertSet('templateVariant', 'standard')
            ->assertDontSee('Format:');
        $this->assertEquals('docx', $component->get('currentFormat'));
    }

    public function test_export_excel_returns_download_stream_for_authenticated_user(): void
    {
        $sampleData = [
            'meeting_info' => [
                'date' => '2026-11-09',
                'time' => '14:00 WIB',
                'location' => 'Meeting Room 1',
                'chairman' => 'Devina',
            ],
            'attendees' => [
                ['name' => 'Devina', 'company' => 'PT Yusen Logistics Interlink Indonesia', 'division' => 'IT'],
                ['name' => 'Budi', 'company' => 'PT Yusen Logistics Interlink Indonesia', 'division' => 'Operation'],
            ],
            'agenda_items' => [
                [
                    'no' => 1,
                    'topic' => 'Evaluasi Operasional',
                    'discussion' => 'Diskusi terkait optimalisasi proses pengiriman dan operasional.',
                    'action_items' => [
                        ['task' => 'Follow up SOP', 'pic' => 'Budi', 'deadline' => '15-Nov-2026'],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('mom.export-excel'), [
            'mom_data' => json_encode($sampleData),
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
