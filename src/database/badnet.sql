create table users(
 id int auto_increment primary key,
 name varchar(255) not null,
 email varchar(255) not null unique,
 email_verified_at datetime null,
 password varchar(255) not null,
 phone varchar(255) null,
 `rank` enum('Beginner','Intermediate','Advanced','Professional') not null default 'Beginner',
 elo_rating int not null default 1200,
 virtual_coins int not null default 5000,
 wins int not null default 0,
 losses int not null default 0,
 bio text null,
 avatar varchar(255) null,
 role enum('user','admin') not null default 'user',
 remember_token varchar(100) null,
 created_at datetime null,
 updated_at datetime null
)engine=innodb;

create table password_reset_tokens(
 email varchar(255) primary key,
 token varchar(255) not null,
 created_at datetime null,
 updated_at datetime null
)engine=innodb;

create table sessions(
 id varchar(255) primary key,
 user_id int null,
 ip_address varchar(45) null,
 user_agent text null,
 payload longtext not null,
 last_activity int not null,
 created_at datetime null,
 updated_at datetime null,
 key user_id_index(user_id),
 key last_activity_index(last_activity)
)engine=innodb;

create table challenges(
 id int auto_increment primary key,
 challenger_id int not null,
 opponent_id int null,
 status enum('open','pending','accepted','rejected','expired') not null default 'open',
 message text null,
 expires_at datetime not null,
 created_at datetime null,
 updated_at datetime null,
 key challenges_challenger_id_foreign(challenger_id),
 key challenges_opponent_id_foreign(opponent_id),
 constraint challenges_challenger_id_foreign foreign key(challenger_id) references users(id) on delete cascade,
 constraint challenges_opponent_id_foreign foreign key(opponent_id) references users(id) on delete cascade
)engine=innodb;

create table matches(
 id int auto_increment primary key,
 player1_id int not null,
 player2_id int null,
 challenge_id int null,
 status enum('open','scheduled','in_progress','completed','cancelled') not null default 'open',
 match_date datetime not null,
 location varchar(255) null,
 player1_score int null,
 player2_score int null,
 winner_id int null,
 elo_change int not null default 0,
 created_at datetime null,
 updated_at datetime null,
 key matches_player1_id_foreign(player1_id),
 key matches_player2_id_foreign(player2_id),
 key matches_challenge_id_foreign(challenge_id),
 key matches_winner_id_foreign(winner_id),
 constraint matches_player1_id_foreign foreign key(player1_id) references users(id) on delete cascade,
 constraint matches_player2_id_foreign foreign key(player2_id) references users(id) on delete cascade,
 constraint matches_challenge_id_foreign foreign key(challenge_id) references challenges(id) on delete set null,
 constraint matches_winner_id_foreign foreign key(winner_id) references users(id) on delete set null
)engine=innodb;

create table join_requests(
 id int auto_increment primary key,
 requestable_type varchar(100) not null,
 requestable_id int not null,
 requester_id int not null,
 status enum('pending','accepted','rejected') not null default 'pending',
 created_at datetime null,
 updated_at datetime null,
 key join_requests_requestable_type_requestable_id_index(requestable_type, requestable_id),
 key join_requests_requester_id_foreign(requester_id),
 constraint join_requests_requester_id_foreign foreign key(requester_id) references users(id) on delete cascade
)engine=innodb;

create table teams(
 id int auto_increment primary key,
 name varchar(255) not null,
 description text null,
 leader_id int not null,
 logo varchar(255) null,
 members_count int not null default 1,
 created_at datetime null,
 updated_at datetime null,
 key teams_leader_id_foreign(leader_id),
 constraint teams_leader_id_foreign foreign key(leader_id) references users(id) on delete cascade
)engine=innodb;

create table team_members(
 id int auto_increment primary key,
 team_id int not null,
 user_id int not null,
 role enum('leader','member') not null default 'member',
 created_at datetime null,
 updated_at datetime null,
 unique key team_members_team_id_user_id_unique(team_id,user_id),
 key team_members_team_id_foreign(team_id),
 key team_members_user_id_foreign(user_id),
 constraint team_members_team_id_foreign foreign key(team_id) references teams(id) on delete cascade,
 constraint team_members_user_id_foreign foreign key(user_id) references users(id) on delete cascade
)engine=innodb;

create table tournaments(
 id int auto_increment primary key,
 name varchar(255) not null,
 description text null,
 organizer_id int not null,
 start_date datetime not null,
 end_date datetime null,
 max_participants int not null default 16,
 status enum('upcoming','in_progress','completed') not null default 'upcoming',
 prize_pool int not null default 0,
 created_at datetime null,
 updated_at datetime null,
 key tournaments_organizer_id_foreign(organizer_id),
 constraint tournaments_organizer_id_foreign foreign key(organizer_id) references users(id) on delete cascade
)engine=innodb;

create table tournament_participants(
 id int auto_increment primary key,
 tournament_id int not null,
 user_id int not null,
 points int not null default 0,
 position int null,
 created_at datetime null,
 updated_at datetime null,
 unique key tournament_participants_tournament_id_user_id_unique(tournament_id,user_id),
 key tournament_participants_tournament_id_foreign(tournament_id),
 key tournament_participants_user_id_foreign(user_id),
 constraint tournament_participants_tournament_id_foreign foreign key(tournament_id) references tournaments(id) on delete cascade,
 constraint tournament_participants_user_id_foreign foreign key(user_id) references users(id) on delete cascade
)engine=innodb;

create table posts(
 id int auto_increment primary key,
 user_id int not null,
 content text not null,
 likes_count int not null default 0,
 created_at datetime null,
 updated_at datetime null,
 key posts_user_id_foreign(user_id),
 constraint posts_user_id_foreign foreign key(user_id) references users(id) on delete cascade
)engine=innodb;

create table comments(
 id int auto_increment primary key,
 post_id int not null,
 user_id int not null,
 content text not null,
 created_at datetime null,
 updated_at datetime null,
 key comments_post_id_foreign(post_id),
 key comments_user_id_foreign(user_id),
 constraint comments_post_id_foreign foreign key(post_id) references posts(id) on delete cascade,
 constraint comments_user_id_foreign foreign key(user_id) references users(id) on delete cascade
)engine=innodb;

create table post_likes(
 id int auto_increment primary key,
 post_id int not null,
 user_id int not null,
 created_at datetime null,
 updated_at datetime null,
 unique key post_likes_post_id_user_id_unique(post_id,user_id),
 key post_likes_post_id_foreign(post_id),
 key post_likes_user_id_foreign(user_id),
 constraint post_likes_post_id_foreign foreign key(post_id) references posts(id) on delete cascade,
 constraint post_likes_user_id_foreign foreign key(user_id) references users(id) on delete cascade
)engine=innodb;

create table bets(
 id int auto_increment primary key,
 user_id int not null,
 match_id int not null,
 bet_on_user_id int not null,
 amount int not null,
 status enum('pending','won','lost') not null default 'pending',
 payout int null,
 created_at datetime null,
 updated_at datetime null,
 key bets_user_id_foreign(user_id),
 key bets_match_id_foreign(match_id),
 key bets_bet_on_user_id_foreign(bet_on_user_id),
 constraint bets_user_id_foreign foreign key(user_id) references users(id) on delete cascade,
 constraint bets_match_id_foreign foreign key(match_id) references matches(id) on delete cascade,
 constraint bets_bet_on_user_id_foreign foreign key(bet_on_user_id) references users(id) on delete cascade
)engine=innodb;

create table notifications(
 id int auto_increment primary key,
 user_id int not null,
 title varchar(255) not null,
 message text not null,
 type varchar(255) not null,
 related_user_id int null,
 is_read tinyint(1) not null default 0,
 created_at datetime null,
 updated_at datetime null,
 key notifications_user_id_foreign(user_id),
 key notifications_related_user_id_foreign(related_user_id),
 constraint notifications_user_id_foreign foreign key(user_id) references users(id) on delete cascade,
 constraint notifications_related_user_id_foreign foreign key(related_user_id) references users(id) on delete set null
)engine=innodb;
