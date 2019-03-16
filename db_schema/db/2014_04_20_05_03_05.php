<?php

class Migration_2014_04_20_05_03_05 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $new_classes = [
            4  => 'group-admins',
            2  => 'group-guests',
            3  => 'group-members',
            1  => 'group-validating',
            5  => 'group-banned',
            7  => 'group-moderators',
            9  => 'group-project_coordinators',
            14 => 'group-global_moderators',
            15 => 'group-comoderators',
            16 => 'group-offenders group-offenders-l1',
            17 => 'group-offenders group-offenders-l2',
            18 => 'group-offenders group-offenders-l3',
            19 => 'group-offenders group-offenders-l4',
            20 => 'group-offenders group-offenders-l5',
            21 => 'group-offenders group-offenders-l6',
            22 => 'group-offenders group-offenders-l7',
            23 => 'group-offenders group-offenders-l8',
            24 => 'group-offenders group-offenders-l9',
            25 => 'group-club_members',
            26 => 'group-veterans',
            27 => 'group-search_bots',
            28 => 'group-developers',
            29 => 'group-special',
            30 => 'group-test',
            31 => 'group-newbies',
            32 => 'group-super_admins',
        ];
        $stmt        = $pdo->prepare('UPDATE ibf_groups SET prefix=:prefix, suffix=:suffix WHERE g_id = :id');
        foreach ($new_classes as $id => $class) {
            $stmt->execute(
                [
                    ':prefix' => sprintf('<span class="%s">', $class),
                    ':suffix' => '</span>',
                    ':id'     => $id
                ]
            );
        }
    }

    public function down(PDO &$pdo)
    {
        $groups_from_db = [
            4  => ['title' => "Администраторы", 'prefix' => "<span class='movedprefix'>", 'suffix' => "</span>"],
            2  => ['title' => "Гости", 'prefix' => "", 'suffix' => ""],
            3  => ['title' => "Участники", 'prefix' => "", 'suffix' => ""],
            1  => ['title' => "Ожидающие доступа", 'prefix' => "", 'suffix' => ""],
            5  => ['title' => "Бан", 'prefix' => "<span style='color:gray'>", 'suffix' => "</span>"],
            7  => ['title' => "Модераторы", 'prefix' => "<span style='color:blue'>", 'suffix' => "</span>"],
            9  => ['title' => "Координаторы проектов", 'prefix' => "<span class='voteprefix'>", 'suffix' => "</span>"],
            14 => ['title' => "Супер модераторы", 'prefix' => "<span style='color:orange;'>", 'suffix' => "</span>"],
            15 => ['title' => "Комодераторы", 'prefix' => "<span style='color:blue'>", 'suffix' => "</span>"],
            16 => [
                'title'  => "Нарушившие правила. Уровень 1",
                'prefix' => "<span style='color:gray'>",
                'suffix' => "</span>"
            ],
            17 => [
                'title'  => "Нарушившие правила. Уровень 2",
                'prefix' => "<span style='color:gray'>",
                'suffix' => "</span>"
            ],
            18 => [
                'title'  => "Нарушившие правила. Уровень 3",
                'prefix' => "<span style='color:gray'>",
                'suffix' => "</span>"
            ],
            19 => [
                'title'  => "Нарушившие правила. Уровень 4",
                'prefix' => "<span style='color:gray'>",
                'suffix' => "</span>"
            ],
            20 => [
                'title'  => "Нарушившие правила. Уровень 5",
                'prefix' => "<span style='color:gray'>",
                'suffix' => "</span>"
            ],
            21 => [
                'title'  => "Нарушившие правила. Уровень 6",
                'prefix' => "<span style='color:gray'>",
                'suffix' => "</span>"
            ],
            22 => [
                'title'  => "Нарушившие правила. Уровень 7",
                'prefix' => "<span style='color:gray'>",
                'suffix' => "</span>"
            ],
            23 => [
                'title'  => "Нарушившие правила. Уровень 8",
                'prefix' => "<span style='color:gray'>",
                'suffix' => "</span>"
            ],
            24 => [
                'title'  => "Нарушившие правила. Уровень 9",
                'prefix' => "<span style='color:gray'>",
                'suffix' => "</span>"
            ],
            25 => [
                'title'  => "Участники клуба Sources.Ru",
                'prefix' => "<span style='color:navy'>",
                'suffix' => "</span>"
            ],
            26 => ['title' => "Ветераны", 'prefix' => "<span style='color:purple'>", 'suffix' => "</span>"],
            27 => ['title' => "Поисковые машины", 'prefix' => "<i>", 'suffix' => "</i>"],
            28 => ['title' => "Developers", 'prefix' => "", 'suffix' => ""],
            29 => ['title' => "Special", 'prefix' => "", 'suffix' => ""],
            30 => ['title' => "Test", 'prefix' => "", 'suffix' => ""],
            31 => ['title' => "Newbie", 'prefix' => "", 'suffix' => ""],
            32 => ['title' => "Суперадминистраторы", 'prefix' => "<span class='movedprefix'>", 'suffix' => "</span>"],
        ];
        $stmt           = $pdo->prepare('UPDATE ibf_groups SET prefix=:prefix, suffix=:suffix WHERE g_id = :id');
        foreach ($groups_from_db as $id => $fields) {
            $stmt->execute([':id' => $id, ':prefix' => $fields['prefix'], ':suffix' => $fields['suffix']]);
        }
    }
}
