<?php

namespace App\Tests\Data\Fixtures;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\Gender;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class UserFixtures extends Fixture
{
    public const UUIDv7_USER_A = '01914b64-e7d9-774f-959b-d3c1077814d2';
    public const UUIDv7_USER_B = '01914b65-4908-7bab-8a93-d3b24dd02e95';
    public const UUIDv7_USER_C = '01914b65-7927-7788-ac4f-e086526e00aa';
    public const UUIDv7_USER_D = '01914b65-a0bf-7520-af38-0fbdf3336b83';
    public const UUIDv7_USER_E = '01914b65-e4db-7203-935f-83f8957cad85';

    public const USER_UUIDv7s = [
        self::UUIDv7_USER_A,
        self::UUIDv7_USER_B,
        self::UUIDv7_USER_C,
        self::UUIDv7_USER_D,
        self::UUIDv7_USER_E,
    ];

    public const EMAIL_USER_A = 'test.user.A@foo.local';
    public const EMAIL_USER_B = 'test.user.B@foo.local';
    public const EMAIL_USER_C = 'test.user.C@foo.local';
    public const EMAIL_USER_D = 'test.user.D@foo.local';
    public const EMAIL_USER_E = 'test.user.E@foo.local';

    public const USER_EMAILS = [
        self::EMAIL_USER_A,
        self::EMAIL_USER_B,
        self::EMAIL_USER_C,
        self::EMAIL_USER_D,
        self::EMAIL_USER_E,
    ];

    /**
     * Create basic set of 5 users
     * with various combination of 
     * attribute values
     * 
     * @return array[string => User]
     */
    public static function get(): array {
        $userA = new User();
        $userA->setId(new Uuid(self::UUIDv7_USER_A));
        $userA->setName('Test');
        $userA->setSurname('User A');
        $userA->setEmail(self::EMAIL_USER_A);
        $userA->setRoles([Role::ADMIN, Role::WORKER]);
        $userA->setGender(Gender::FEMALE);
        $userA->setActive(true);

        $userB = new User();
        $userA->setId(new Uuid(self::UUIDv7_USER_B));
        $userB->setName('Test');
        $userB->setSurname('User B');
        $userB->setEmail(self::EMAIL_USER_B);
        $userB->setRoles([Role::USER]);
        $userB->setGender(Gender::MALE);
        $userB->setActive(true);

        $userC = new User();
        $userA->setId(new Uuid(self::UUIDv7_USER_C));
        $userC->setName('Test');
        $userC->setSurname('User C');
        $userC->setEmail(self::EMAIL_USER_C);
        $userC->setRoles([Role::USER, Role::WORKER]);
        $userC->setGender(Gender::FEMALE);
        $userC->setActive(true);

        $userD = new User();
        $userA->setId(new Uuid(self::UUIDv7_USER_D));
        $userD->setName('Test');
        $userD->setSurname('User D');
        $userD->setEmail(self::EMAIL_USER_D);
        $userD->setRoles([Role::USER, Role::ADMIN]);
        $userD->setGender(Gender::MALE);
        $userD->setActive(true);
        $userD->setNote('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas placerat orci eget sem consectetur faucibus.');
        
        $userE = new User();
        $userA->setId(new Uuid(self::UUIDv7_USER_E));
        $userE->setName('Test');
        $userE->setSurname('User E');
        $userE->setEmail(self::EMAIL_USER_E);
        $userE->setRoles([Role::USER, Role::ADMIN, Role::WORKER]);
        $userE->setGender(Gender::FEMALE);
        $userE->setActive(false);
        $userE->setNote('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas placerat orci eget sem consectetur faucibus.');

        return [
            self::EMAIL_USER_A => $userA,
            self::EMAIL_USER_B => $userB,
            self::EMAIL_USER_C => $userC,
            self::EMAIL_USER_D => $userD,
            self::EMAIL_USER_E => $userE
        ];
    }

    /**
     * Load fixture entities into database
     * 
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        // get fixtures data as entities
        // and persist them
        foreach(self::get() as $entity) {
            $manager->persist($entity);
        }

        $manager->flush();
    }
}
