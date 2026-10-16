<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTestimonialSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_form_accepts_only_name_and_message_and_queues_for_review(): void
    {
        $response = $this->post(route('store.testimonials.store'), [
            'name' => 'Rina',
            'message' => 'Belanja di Sinergi Store menyenangkan.',
        ]);

        $response->assertRedirect(route('store.home') . '#testimonials');
        $response->assertSessionHas('testimonial_success');

        $this->assertDatabaseHas('testimonials', [
            'name' => 'Rina',
            'message' => 'Belanja di Sinergi Store menyenangkan.',
            'photo' => null,
            'is_active' => false,
            'is_approved' => false,
        ]);
    }

    public function test_public_testimonial_requires_name_and_message(): void
    {
        $response = $this->from(route('store.home') . '#testimonials')
            ->post(route('store.testimonials.store'), [
                'name' => '',
                'message' => '',
            ]);

        $response->assertRedirect(route('store.home') . '#testimonials');
        $response->assertSessionHasErrors(['name', 'message']);
        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_admin_can_review_and_publish_a_public_testimonial(): void
    {
        $testimonial = Testimonial::create([
            'name' => 'Rina',
            'message' => 'Belanja di Sinergi Store menyenangkan.',
            'is_active' => false,
            'is_approved' => false,
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->put(route('admin.testimonials.update', $testimonial), [
                'name' => $testimonial->name,
                'message' => $testimonial->message,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.testimonials.index'));
        $this->assertDatabaseHas('testimonials', [
            'id' => $testimonial->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Rina')
            ->assertSee('Belanja di Sinergi Store menyenangkan.');
    }

    public function test_pending_public_testimonials_are_filterable_in_admin(): void
    {
        Testimonial::create([
            'name' => 'Rina',
            'message' => 'Belanja di Sinergi Store menyenangkan.',
            'is_active' => false,
            'is_approved' => false,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.testimonials.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Menunggu persetujuan')
            ->assertSee('Rina')
            ->assertSee('Tinjau');
    }
}
