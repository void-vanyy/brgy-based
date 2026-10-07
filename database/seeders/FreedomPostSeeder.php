<?php

namespace Database\Seeders;

use App\Models\FreedomPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class FreedomPostSeeder extends Seeder
{
    /**
     * Eight anonymous posts covering the four wall topics.
     */
    public function run(): void
    {
        $residents = User::query()->where('role', 'resident')->orderBy('id')->get();

        if ($residents->isEmpty()) {
            return; // UserSeeder must run first.
        }

        $posts = [
            ['topic' => 'suggestion', 'reactions' => 42, 'message' => 'Suggestion: can we have a "senior citizen hour" at the barangay hall every Monday morning? My lola waits two hours for her clearance because the counter gets crowded after 9 AM.'],
            ['topic' => 'concern',   'reactions' => 31, 'message' => 'The lights along the footbridge to the market have been out since last month. It is really dark when we come home from the night shift — please check.'],
            ['topic' => 'praise',    'reactions' => 57, 'message' => 'Shout out to the tanod team who helped push our tricycle out of the flood last night and brought us home. Maraming salamat po!'],
            ['topic' => 'shoutout',  'reactions' => 26, 'message' => 'Congratulations to Purok 3 for winning the Sigla Cup! Best game this year, and thank you to everyone who sold snacks for the youth league.'],
            ['topic' => 'suggestion', 'reactions' => 19, 'message' => 'Can the recycling collection schedule be posted on the announcements page? We only find out from the tarpaulin when the truck is already outside.'],
            ['topic' => 'concern',   'reactions' => 35, 'message' => 'Water pressure drops every morning from 6 to 8 AM in Zone 2. Several households already filed complaints — any update from the water district?'],
            ['topic' => 'praise',    'reactions' => 48, 'message' => 'Thank you for the free medical mission last quarter. My father had his blood pressure checked and the dentists were very kind to the kids.'],
            ['topic' => 'shoutout',  'reactions' => 23, 'message' => 'To whoever returned the wallet near the covered court yesterday — the barangay is lucky to have honest residents. Keep it up, Sigla!'],
        ];

        foreach ($posts as $index => $post) {
            FreedomPost::query()->firstOrCreate(
                ['message' => $post['message']],
                [
                    'user_id'      => $residents[$index % $residents->count()]->id,
                    'topic'        => $post['topic'],
                    'is_anonymous' => true,
                    'reactions'    => $post['reactions'],
                ]
            );
        }
    }
}
