<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str; 

class BlogTest extends TestCase
{
    use RefreshDatabase;

   
    

    public function testSuperAdminCanCreateBlog()
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
        ]);
    
        $token = JWTAuth::fromUser($user);
    
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/blogs', [
                'title' => 'Test Blog',
                'content' => '<p>Content</p>',
                'tags' => ['test', 'blog'], // Pass arrays directly
                'imageUrl' => ['http://example.com/image.jpg'], // Pass array directly
            ]);
    
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status_code',
            'message',
            'data' => [
                'id',
                'title',
                'content',
                'tags',
                'imageUrl',
                'author',
                'created_at',
                'updated_at',
            ],
        ]);
    }
    

    
    
    
    

    public function testNonSuperAdminCannotCreateBlog()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/blogs', [
                'title' => 'Test Blog',
                'content' => '<p>Content</p>',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Forbidden',
            'status_code' => 403,
        ]);
    }

    public function testUnauthorizedCannotCreateBlog()
    {
        $response = $this->postJson('/api/v1/blogs', [
            'title' => 'Test Blog',
            'content' => '<p>Content</p>',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testValidationError()
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/blogs', []);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'status_code' => 400,
        ]);
    }
}
