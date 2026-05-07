-- ============================================================
-- BadNet Database Seed Data
-- Sample data for development and testing
-- ============================================================

-- ============================================================
-- SEED: Users
-- ============================================================

INSERT INTO users (
  email, username, password_hash, first_name, last_name, 
  bio, location, rating, wins, losses, is_verified, role
) VALUES
  -- Admin user
  ('admin@badnet.com', 'admin', 
   '$2b$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 
   'Admin', 'User', 'Platform Administrator', 'New York', 1500.00, 50, 5, true, 'admin'),
  
  -- Regular players
  ('john.doe@email.com', 'johndoe', 
   '$2b$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 
   'John', 'Doe', 'Badminton enthusiast', 'New York', 1350.50, 25, 10, true, 'user'),
  
  ('jane.smith@email.com', 'janesmith', 
   '$2b$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 
   'Jane', 'Smith', 'Competitive player', 'Los Angeles', 1420.25, 32, 8, true, 'user'),
  
  ('mike.johnson@email.com', 'mikej', 
   '$2b$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 
   'Mike', 'Johnson', 'Weekend warrior', 'Chicago', 1200.00, 15, 12, true, 'user'),
  
  ('sarah.williams@email.com', 'sarahw', 
   '$2b$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 
   'Sarah', 'Williams', 'Casual player', 'Houston', 1150.75, 8, 20, true, 'user'),
  
  ('david.brown@email.com', 'davidb', 
   '$2b$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 
   'David', 'Brown', 'Tournament organizer', 'Phoenix', 1380.00, 28, 9, true, 'moderator'),
  
  ('emma.davis@email.com', 'emmad', 
   '$2b$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 
   'Emma', 'Davis', 'Professional player', 'Philadelphia', 1500.00, 45, 3, true, 'user'),
  
  ('thomas.wilson@email.com', 'thomasw', 
   '$2b$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 
   'Thomas', 'Wilson', 'Social player', 'San Antonio', 1250.50, 18, 15, true, 'user');

-- ============================================================
-- SEED: Teams
-- ============================================================

INSERT INTO teams (name, description, captain_id, wins, losses, location, is_public) VALUES
  ('City Champions', 'Competitive local badminton team', 
   (SELECT id FROM users WHERE username = 'johndoe'), 20, 5, 'New York', true),
  
  ('Golden Racquets', 'Elite tournament team', 
   (SELECT id FROM users WHERE username = 'janesmith'), 25, 3, 'Los Angeles', true),
  
  ('Urban Shuttlers', 'Community-based team', 
   (SELECT id FROM users WHERE username = 'davidb'), 15, 8, 'Phoenix', true),
  
  ('The Smashers', 'Fun and friendly team', 
   (SELECT id FROM users WHERE username = 'emmad'), 18, 6, 'Philadelphia', true);

-- ============================================================
-- SEED: Team Members
-- ============================================================

INSERT INTO team_members (team_id, user_id, role, status) VALUES
  ((SELECT id FROM teams WHERE name = 'City Champions'), 
   (SELECT id FROM users WHERE username = 'johndoe'), 'captain', 'active'),
  ((SELECT id FROM teams WHERE name = 'City Champions'), 
   (SELECT id FROM users WHERE username = 'mikej'), 'member', 'active'),
  ((SELECT id FROM teams WHERE name = 'City Champions'), 
   (SELECT id FROM users WHERE username = 'sarahw'), 'member', 'active'),
  
  ((SELECT id FROM teams WHERE name = 'Golden Racquets'), 
   (SELECT id FROM users WHERE username = 'janesmith'), 'captain', 'active'),
  ((SELECT id FROM teams WHERE name = 'Golden Racquets'), 
   (SELECT id FROM users WHERE username = 'emmad'), 'member', 'active'),
  
  ((SELECT id FROM teams WHERE name = 'Urban Shuttlers'), 
   (SELECT id FROM users WHERE username = 'davidb'), 'captain', 'active'),
  ((SELECT id FROM teams WHERE name = 'Urban Shuttlers'), 
   (SELECT id FROM users WHERE username = 'thomasw'), 'member', 'active'),
  
  ((SELECT id FROM teams WHERE name = 'The Smashers'), 
   (SELECT id FROM users WHERE username = 'emmad'), 'captain', 'active'),
  ((SELECT id FROM teams WHERE name = 'The Smashers'), 
   (SELECT id FROM users WHERE username = 'johndoe'), 'member', 'active');

-- ============================================================
-- SEED: Friendships
-- ============================================================

INSERT INTO friendships (user_id_1, user_id_2, status, initiated_by_id) VALUES
  ((SELECT id FROM users WHERE username = 'johndoe'), 
   (SELECT id FROM users WHERE username = 'janesmith'), 
   'accepted', 
   (SELECT id FROM users WHERE username = 'johndoe')),
  
  ((SELECT id FROM users WHERE username = 'johndoe'), 
   (SELECT id FROM users WHERE username = 'mikej'), 
   'accepted', 
   (SELECT id FROM users WHERE username = 'mikej')),
  
  ((SELECT id FROM users WHERE username = 'janesmith'), 
   (SELECT id FROM users WHERE username = 'emmad'), 
   'accepted', 
   (SELECT id FROM users WHERE username = 'janesmith')),
  
  ((SELECT id FROM users WHERE username = 'davidb'), 
   (SELECT id FROM users WHERE username = 'emmad'), 
   'accepted', 
   (SELECT id FROM users WHERE username = 'davidb')),
  
  ((SELECT id FROM users WHERE username = 'mikej'), 
   (SELECT id FROM users WHERE username = 'sarahw'), 
   'pending', 
   (SELECT id FROM users WHERE username = 'mikej'));

-- ============================================================
-- SEED: Matches (Completed Games)
-- ============================================================

INSERT INTO matches (
  title, description, player1_id, player2_id, location, 
  match_date, player1_score, player2_score, status, 
  best_of_x, created_by_id
) VALUES
  ('Friendly Match: John vs Jane', 'Casual weekend match',
   (SELECT id FROM users WHERE username = 'johndoe'),
   (SELECT id FROM users WHERE username = 'janesmith'),
   'Central Park Courts', '2024-04-15 14:00:00+00:00',
   21, 18, 'completed', 1,
   (SELECT id FROM users WHERE username = 'johndoe')),
  
  ('Tournament Practice: Mike vs Emma', 'Preparation match',
   (SELECT id FROM users WHERE username = 'mikej'),
   (SELECT id FROM users WHERE username = 'emmad'),
   'Indoor Sports Complex', '2024-04-14 16:00:00+00:00',
   15, 21, 'completed', 1,
   (SELECT id FROM users WHERE username = 'davidb')),
  
  ('League Match: John vs David', 'Regular season game',
   (SELECT id FROM users WHERE username = 'johndoe'),
   (SELECT id FROM users WHERE username = 'davidb'),
   'Community Badminton Club', '2024-04-13 18:00:00+00:00',
   20, 22, 'completed', 1,
   (SELECT id FROM users WHERE username = 'johndoe')),
  
  ('Scheduled Match: Sarah vs Thomas', 'Upcoming match',
   (SELECT id FROM users WHERE username = 'sarahw'),
   (SELECT id FROM users WHERE username = 'thomasw'),
   'Recreational Center', '2024-05-01 15:00:00+00:00',
   NULL, NULL, 'scheduled', 1,
   (SELECT id FROM users WHERE username = 'sarahw'));

-- ============================================================
-- SEED: Challenges
-- ============================================================

INSERT INTO challenges (
  challenger_id, opponent_id, title, message, status, expires_at
) VALUES
  ('John challenges Jane to a match!', 'Let\'s see who\'s better',
   (SELECT id FROM users WHERE username = 'johndoe'),
   (SELECT id FROM users WHERE username = 'janesmith'),
   'pending', CURRENT_TIMESTAMP + INTERVAL '30 days'),
  
  ('Emma challenges David to a match!', 'Tournament prep',
   (SELECT id FROM users WHERE username = 'emmad'),
   (SELECT id FROM users WHERE username = 'davidb'),
   'accepted', CURRENT_TIMESTAMP + INTERVAL '25 days');

-- ============================================================
-- SEED: Tournaments
-- ============================================================

INSERT INTO tournaments (
  name, description, start_date, end_date, registration_deadline,
  structure, status, max_participants, prize_pool, organizer_id, is_public
) VALUES
  ('Spring Badminton Championship', 'Annual spring tournament for all levels',
   '2024-05-15 08:00:00+00:00', '2024-05-20 18:00:00+00:00', '2024-05-10 23:59:59+00:00',
   'single_elimination', 'registration', 32, 5000.00,
   (SELECT id FROM users WHERE username = 'davidb'), true),
  
  ('Summer Series 2024', 'Monthly tournament series',
   '2024-06-01 09:00:00+00:00', '2024-06-30 17:00:00+00:00', '2024-05-25 23:59:59+00:00',
   'round_robin', 'draft', 16, 2000.00,
   (SELECT id FROM users WHERE username = 'admin'), true),
  
  ('City League Finals', 'Finals of the seasonal league',
   '2024-07-01 10:00:00+00:00', '2024-07-10 19:00:00+00:00', '2024-06-20 23:59:59+00:00',
   'double_elimination', 'draft', 24, 3000.00,
   (SELECT id FROM users WHERE username = 'davidb'), true);

-- ============================================================
-- SEED: Tournament Participants
-- ============================================================

INSERT INTO tournament_participants (tournament_id, user_id, status, seed_position, registered_at) VALUES
  -- Spring Championship participants
  ((SELECT id FROM tournaments WHERE name = 'Spring Badminton Championship'),
   (SELECT id FROM users WHERE username = 'johndoe'), 'registered', 1,
   '2024-04-20 10:00:00+00:00'),
  
  ((SELECT id FROM tournaments WHERE name = 'Spring Badminton Championship'),
   (SELECT id FROM users WHERE username = 'janesmith'), 'confirmed', 2,
   '2024-04-21 11:00:00+00:00'),
  
  ((SELECT id FROM tournaments WHERE name = 'Spring Badminton Championship'),
   (SELECT id FROM users WHERE username = 'emmad'), 'confirmed', 3,
   '2024-04-22 09:30:00+00:00'),
  
  ((SELECT id FROM tournaments WHERE name = 'Spring Badminton Championship'),
   (SELECT id FROM users WHERE username = 'davidb'), 'registered', 4,
   '2024-04-20 14:00:00+00:00');

-- ============================================================
-- SEED: Posts (Social Feed)
-- ============================================================

INSERT INTO posts (author_id, content, visibility, match_id) VALUES
  ((SELECT id FROM users WHERE username = 'johndoe'),
   'Had an amazing match with Jane today! Great competition and learned a lot.', 
   'public', (SELECT id FROM matches WHERE title LIKE 'Friendly Match: John vs Jane')),
  
  ((SELECT id FROM users WHERE username = 'emmad'),
   'Excited to announce I\'m organizing the Spring Championship! Sign up now!',
   'public', NULL),
  
  ((SELECT id FROM users WHERE username = 'davidb'),
   'Just finished coaching a new group of players. Love seeing enthusiasm for the sport!',
   'public', NULL),
  
  ((SELECT id FROM users WHERE username = 'janesmith'),
   'Training hard for upcoming tournaments. Any tips from experienced players?',
   'friends', NULL);

-- ============================================================
-- SEED: Comments on Posts
-- ============================================================

INSERT INTO comments (post_id, author_id, content) VALUES
  ((SELECT id FROM posts WHERE content LIKE 'Had an amazing match%'),
   (SELECT id FROM users WHERE username = 'janesmith'),
   'Thanks John! You played great too. Let\'s do it again soon!'),
  
  ((SELECT id FROM posts WHERE content LIKE 'Had an amazing match%'),
   (SELECT id FROM users WHERE username = 'mikej'),
   'Nice job! You two are getting really good!'),
  
  ((SELECT id FROM posts WHERE content LIKE 'Excited to announce%'),
   (SELECT id FROM users WHERE username = 'johndoe'),
   'Already signed up! Can\'t wait for this tournament!'),
  
  ((SELECT id FROM posts WHERE content LIKE 'Training hard%'),
   (SELECT id FROM users WHERE username = 'emmad'),
   'Focus on your footwork and keep your shots consistent. You\'ll do great!');

-- ============================================================
-- SEED: Post Likes
-- ============================================================

INSERT INTO post_likes (post_id, user_id) VALUES
  ((SELECT id FROM posts WHERE content LIKE 'Had an amazing match%'),
   (SELECT id FROM users WHERE username = 'janesmith')),
  
  ((SELECT id FROM posts WHERE content LIKE 'Had an amazing match%'),
   (SELECT id FROM users WHERE username = 'davidb')),
  
  ((SELECT id FROM posts WHERE content LIKE 'Excited to announce%'),
   (SELECT id FROM users WHERE username = 'johndoe')),
  
  ((SELECT id FROM posts WHERE content LIKE 'Excited to announce%'),
   (SELECT id FROM users WHERE username = 'janesmith')),
  
  ((SELECT id FROM posts WHERE content LIKE 'Training hard%'),
   (SELECT id FROM users WHERE username = 'emmad'));

-- ============================================================
-- SEED: Bets
-- ============================================================

INSERT INTO bets (bettor_id, match_id, prediction, amount, odds, potential_return, status) VALUES
  ((SELECT id FROM users WHERE username = 'johndoe'),
   (SELECT id FROM matches WHERE title LIKE 'Friendly Match%'),
   'jane_smith_wins', 50.00, 1.80, 90.00, 'lost'),
  
  ((SELECT id FROM users WHERE username = 'mikej'),
   (SELECT id FROM matches WHERE title LIKE 'Tournament Practice%'),
   'emma_wins', 100.00, 1.50, 150.00, 'won'),
  
  ((SELECT id FROM users WHERE username = 'sarahw'),
   (SELECT id FROM matches WHERE title LIKE 'League Match%'),
   'john_wins', 25.00, 1.95, 48.75, 'won'),
  
  ((SELECT id FROM users WHERE username = 'thomasw'),
   (SELECT id FROM matches WHERE title LIKE 'Scheduled Match%'),
   'sarah_williams_wins', 75.00, 2.10, 157.50, 'pending');

-- ============================================================
-- SEED: Notifications
-- ============================================================

INSERT INTO notifications (user_id, type, title, message, related_entity_type, related_entity_id, actor_id, is_read) VALUES
  ((SELECT id FROM users WHERE username = 'janesmith'),
   'challenge_received', 'New Challenge', 'John Doe challenged you to a match!',
   'challenge', (SELECT id FROM challenges WHERE challenger_id = (SELECT id FROM users WHERE username = 'johndoe')),
   (SELECT id FROM users WHERE username = 'johndoe'), false),
  
  ((SELECT id FROM users WHERE username = 'emmad'),
   'match_result', 'Match Completed', 'Your match against Mike Johnson is complete!',
   'match', (SELECT id FROM matches WHERE title LIKE 'Tournament Practice%'), NULL, false),
  
  ((SELECT id FROM users WHERE username = 'johndoe'),
   'comment_on_post', 'New Comment', 'Jane Smith commented on your post',
   'post', (SELECT id FROM posts WHERE content LIKE 'Had an amazing match%'),
   (SELECT id FROM users WHERE username = 'janesmith'), true),
  
  ((SELECT id FROM users WHERE username = 'davidb'),
   'tournament_registered', 'Tournament Registration', 'John Doe registered for Spring Championship',
   'tournament', (SELECT id FROM tournaments WHERE name = 'Spring Badminton Championship'),
   (SELECT id FROM users WHERE username = 'johndoe'), false);

-- ============================================================
-- SEED: User Rating History
-- ============================================================

INSERT INTO user_rating_history (user_id, old_rating, new_rating, change_amount, match_id, reason) VALUES
  ((SELECT id FROM users WHERE username = 'johndoe'), 1340.00, 1350.50, 10.50,
   (SELECT id FROM matches WHERE title LIKE 'Friendly Match%'), 'match_win'),
  
  ((SELECT id FROM users WHERE username = 'janesmith'), 1410.25, 1420.25, 10.00,
   (SELECT id FROM matches WHERE title LIKE 'Friendly Match%'), 'match_win'),
  
  ((SELECT id FROM users WHERE username = 'mikej'), 1210.00, 1200.00, -10.00,
   (SELECT id FROM matches WHERE title LIKE 'Tournament Practice%'), 'match_loss'),
  
  ((SELECT id FROM users WHERE username = 'emmad'), 1495.00, 1500.00, 5.00,
   (SELECT id FROM matches WHERE title LIKE 'Tournament Practice%'), 'match_win');

-- ============================================================
-- SEED: Activity Log
-- ============================================================

INSERT INTO activity_log (user_id, action, entity_type, entity_id, amount, ip_address) VALUES
  ((SELECT id FROM users WHERE username = 'johndoe'), 'match_created', 'match',
   (SELECT id FROM matches WHERE title LIKE 'Friendly Match%'), NULL, '192.168.1.100'),
  
  ((SELECT id FROM users WHERE username = 'johndoe'), 'bet_placed', 'bet',
   (SELECT id FROM bets WHERE bettor_id = (SELECT id FROM users WHERE username = 'johndoe') LIMIT 1),
   50.00, '192.168.1.100'),
  
  ((SELECT id FROM users WHERE username = 'emmad'), 'tournament_created', 'tournament',
   (SELECT id FROM tournaments WHERE name = 'Spring Badminton Championship'),
   5000.00, '192.168.1.101'),
  
  ((SELECT id FROM users WHERE username = 'davidb'), 'post_created', 'post',
   (SELECT id FROM posts WHERE content LIKE 'Just finished coaching%'),
   NULL, '192.168.1.102');

-- ============================================================
-- SEED DATA LOAD COMPLETE
-- ============================================================

-- Verify data load
-- SELECT COUNT(*) as user_count FROM users;
-- SELECT COUNT(*) as match_count FROM matches;
-- SELECT COUNT(*) as post_count FROM posts;
-- SELECT COUNT(*) as comment_count FROM comments;
-- SELECT COUNT(*) as bet_count FROM bets;
-- SELECT COUNT(*) as notification_count FROM notifications;
