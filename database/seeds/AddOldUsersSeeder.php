<?php

use Illuminate\Database\Seeder;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection;

class AddOldUsersSeeder extends Seeder
{
    private $generator;

    function __construct()
    {
        $this->generator =  \Faker\Factory::create();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = DB::table('cip_users')->orderBy('id', 'ASC')->get();
        $errors = [];
        
        foreach ($data as $key => $value) {
            try {
                $this->insertCipUser($value);
            } catch (\Throwable $th) {
                $errors[] = [
                    'value' => $value,
                    'error' => $th
                ];
            }

            var_dump($key);
        }
        file_put_contents('errors.log', json_encode($errors));
    }

    public function insertCipUser(stdClass $value)
    {
        $pass = $this->generator->password();
        $user_id = DB::table('users')->insert([
            'name' => $value->name,
            'email' => $value->email,
            'password' => bcrypt($pass),
            'created_at' => new DateTime()
        ]);

        DB::table('user_to_cip_user')->insert([
            'created_at' => new DateTime(),
            'user_id' => $user_id,
            'cip_user_id' => $value->id,
            'mig_password' => $pass
        ]);
    }

    public function insertCipUsers(Collection $data)
    {
        $res = DB::table('users')
            ->insert(
                $data->map(function ($e) {
                    return [
                        'name' => $e->name,
                        'email' => $e->email,
                        'password' => bcrypt('secret'),
                        'created_at' => new DateTime()
                    ];
                })->toArray()
            );
    }
}
