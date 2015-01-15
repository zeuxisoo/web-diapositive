<?php
namespace Diapositive\Controllers;

use Zeuxisoo\Core\Validator;
use Diapositive\Foundations\Controller;
use Diapositive\Foundations\Model;
use Diapositive\Models;
use Diapositive\Helpers\Authorize;
use Diapositive\Helpers\Secure;

class HomeController extends Controller {

    public function index() {
        $this->render('home/index.html');
    }

    public function signup() {
        if ($this->request->isPost() === true) {
            $username = $this->request->post('username');
            $email    = $this->request->post('email');
            $password = $this->request->post('password');

            $validator = Validator::factory($this->request->post());
            $validator->add('username', 'Please enter username')->rule('required')
                      ->add('email', 'Please enter email')->rule('required')
                      ->add('password', 'Please enter password')->rule('required')
                      ->add('email', 'Invalid email address')->rule('valid_email')
                      ->add('password', 'Password length must more than 8 chars')->rule('min_length', 8)
                      ->add('username', 'Username only support A-Z,a-z,0-9 and _')->rule('match_pattern', '/^[A-Za-z0-9_]+$/')
                      ->add('username', 'Username length must more than 4 chars')->rule('min_length', 4);

            $valid_type    = 'error';
            $valid_message = '';

            if ($validator->inValid() === true) {
                $valid_message = $validator->firstError();
            }else if (Models\User::where('username', $username)->findOne() !== false) {
                $valid_message = 'Username already exists';
            }else if (Models\User::where('email', $email)->findOne() !== false) {
                $valid_message = 'Email already exists';
            }else{
                Model::factory('User')->create([
                    'username'  => $username,
                    'email'     => $email,
                    'password'  => password_hash($password, PASSWORD_BCRYPT),
                    'create_at' => time()
                ])->save();

                $valid_type    = "success";
                $valid_message = "Thank for you registeration. Your account already created";
            }

            $this->flash($valid_type, $valid_message);
            $this->redirectTo('index.signup');
        }else{
            $this->render('home/signup.html');
        }
    }

    public function signin() {
        if ($this->request->isPost() === true) {
            $account  = $this->request->post('account');
            $password = $this->request->post('password');
            $remember = $this->request->post('remember');

            $validator = Validator::factory($this->request->post());
            $validator->add('account', 'Please enter account')->rule('required')
                      ->add('password', 'Please enter password')->rule('required');

            $valid_type     = 'error';
            $valid_message  = '';
            $valid_redirect = "index.signin";

            if ($validator->inValid() === true) {
                $valid_message = $validator->firstError();
            }else{
                if (strpos($account, '@') === false) {
                    $user = Models\User::where('username', $account)->findOne();
                }else{
                    $user = Models\User::where('email', $account)->findOne();
                }

                if (empty($user->username) === true) {
                    $valid_message = 'The user not exists';
                }else if (password_verify($password, $user->password) === false) {
                    $valid_message = 'Password not match';
                }else{
                    if ($remember === 'y') {
                        $config       = $this->app->config('app.config');
                        $signin_token = Secure::randomString();

                        $this->app->setCookie(
                            $config['remember']['name'],
                            Secure::createKey($user->id, $signin_token, $config['cookie']['secret_key']),
                            time() + $config['remember']['life_time']
                        );
                    }

                    Authorize::initLoginSession($user);

                    $valid_type     = "success";
                    $valid_message  = "Welcome back";
                    $valid_redirect = "index.index";
                }
            }

            $this->flash($valid_type, $valid_message);
            $this->redirectTo($valid_redirect);
        }else{
            $this->render('home/signin.html');
        }
    }

    public function signout() {
        Authorize::resetLoginSession($this->app);

        $this->redirectTo('index.index');
    }

}
