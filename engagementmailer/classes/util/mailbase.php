<?php

namespace local_engagementmailer\util;

abstract class mailbase {
    abstract public function send($mailer, $student);
    abstract public function send_bulk($mailer, $student);

    protected function replace_body_variables($user, $message) {
        global $CFG, $SITE;

        $fields = [
            'username' => 'username',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'fullname' => 'fullname',
            'email' => 'email',
            'address' => 'address',
            'country' => 'country',
            'city' => 'city',
            'phone' => 'phone1',
            'cellphone' => 'phone2',
            'url' => 'wwwroot',
            'sitename' => 'fullname'
        ];

        $values = [];
        foreach ($fields as $key => $field) {
            switch($key) {
                case 'country':
                    $values[$key] = !empty($user->country) ? get_string($user->country, 'countries') : '';
                    break;
                case 'fullname':
                    $values[$key] = fullname($user);
                    break;
                case 'url':
                    $values[$key] = $CFG->{$field};
                    break;
                case 'sitename':
                    $values[$key] = $SITE->{$field};
                    break;
                default:
                    $values[$key] = $user->{$field};
            }
        }

        foreach ($values as $field => $value) {
            $message = str_replace('[['.$field.']]', $value, $message);
        }

        return $message;
    }
}