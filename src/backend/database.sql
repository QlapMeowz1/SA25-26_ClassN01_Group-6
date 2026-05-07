-- ============================================================
-- BadNet - Badminton Social Platform Database Schema
-- PostgreSQL 13+
-- ============================================================

-- ============================================================
-- ENUMS (Custom Data Types)
-- ============================================================

-- User role enum
CREATE TYPE user_role AS ENUM ('user', 'admin', 'moderator');

-- Match status enum
CREATE TYPE match_status AS ENUM ('scheduled', 'ongoing', 'completed', 'cancelled', 'postponed');

-- Challenge status enum
CREATE TYPE challenge_status AS ENUM ('pending', 'accepted', 'declined', 'completed');

-- Tournament status enum
CREATE TYPE tournament_status AS ENUM ('draft', 'registration', 'ongoing', 'completed', 'cancelled');

-- Tournament structure enum
CREATE TYPE tournament_structure AS ENUM ('single_elimination', 'double_elimination', 'round_robin', 'group_stage');

-- Bet status enum
CREATE TYPE bet_status AS ENUM ('pending', 'won', 'lost', 'cancelled', 'refunded');

-- Post visibility enum
CREATE TYPE post_visibility AS ENUM ('public', 'friends', 'private');

-- Notification type enum
CREATE TYPE notification_type AS ENUM (
  'match_created', 
  'challenge_received', 
  'challenge_accepted', 
  'team_invite', 
  'tournament_registered', 
  'match_result', 
  'comment_on_post', 
  'post_liked', 
  'friend_request'
);

-- ============================================================
-- CORE USERS TABLE
-- ============================================================

CREATE TABLE users (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  email VARCHAR(255) NOT NULL UNIQUE,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  
  -- Profile Information
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  bio TEXT,
  avatar_url VARCHAR(500),
  phone VARCHAR(20),
  date_of_birth DATE,
  location VARCHAR(255),
  
  -- Account Status
  is_active BOOLEAN NOT NULL DEFAULT true,
  is_verified BOOLEAN NOT NULL DEFAULT false,
  role user_role NOT NULL DEFAULT 'user',
  
  -- Player Statistics
  rating DECIMAL(5, 2) NOT NULL DEFAULT 1200.00,
  wins INTEGER NOT NULL DEFAULT 0,
  losses INTEGER NOT NULL DEFAULT 0,
  
  -- Social Stats
  followers_count INTEGER NOT NULL DEFAULT 0,
  following_count INTEGER NOT NULL DEFAULT 0,
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at TIMESTAMP WITH TIME ZONE,
  
  -- Constraints
  CONSTRAINT users_email_format CHECK (email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$'),
  CONSTRAINT users_rating_range CHECK (rating >= 0),
  CONSTRAINT users_stats_non_negative CHECK (wins >= 0 AND losses >= 0)
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_is_active ON users(is_active);
CREATE INDEX idx_users_rating ON users(rating DESC);

-- ============================================================
-- MATCHES TABLE
-- ============================================================

CREATE TABLE matches (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  title VARCHAR(255) NOT NULL,
  description TEXT,
  
  -- Participants
  player1_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  player2_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Match Details
  location VARCHAR(255) NOT NULL,
  match_date TIMESTAMP WITH TIME ZONE NOT NULL,
  duration_minutes INTEGER,
  
  -- Score
  player1_score INTEGER CHECK (player1_score >= 0),
  player2_score INTEGER CHECK (player2_score >= 0),
  status match_status NOT NULL DEFAULT 'scheduled',
  
  -- Game Rules
  best_of_x INTEGER NOT NULL DEFAULT 3 CHECK (best_of_x IN (1, 3, 5)),
  
  -- Metadata
  created_by_id UUID NOT NULL REFERENCES users(id) ON DELETE SET NULL,
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  -- Constraints
  CONSTRAINT players_must_differ CHECK (player1_id != player2_id),
  CONSTRAINT score_only_when_completed CHECK (
    (status != 'completed' AND player1_score IS NULL AND player2_score IS NULL) OR
    (status = 'completed' AND player1_score IS NOT NULL AND player2_score IS NOT NULL)
  )
);

CREATE INDEX idx_matches_player1 ON matches(player1_id);
CREATE INDEX idx_matches_player2 ON matches(player2_id);
CREATE INDEX idx_matches_status ON matches(status);
CREATE INDEX idx_matches_date ON matches(match_date);
CREATE INDEX idx_matches_created_by ON matches(created_by_id);

-- ============================================================
-- CHALLENGES TABLE
-- ============================================================

CREATE TABLE challenges (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  challenger_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  opponent_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Challenge Details
  title VARCHAR(255),
  message TEXT,
  status challenge_status NOT NULL DEFAULT 'pending',
  
  -- Related Match
  match_id UUID REFERENCES matches(id) ON DELETE SET NULL,
  
  -- Expiration (challenge expires after 30 days if not accepted)
  expires_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP + INTERVAL '30 days',
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  accepted_at TIMESTAMP WITH TIME ZONE,
  declined_at TIMESTAMP WITH TIME ZONE,
  
  -- Constraints
  CONSTRAINT challenger_must_differ CHECK (challenger_id != opponent_id)
);

CREATE INDEX idx_challenges_challenger ON challenges(challenger_id);
CREATE INDEX idx_challenges_opponent ON challenges(opponent_id);
CREATE INDEX idx_challenges_status ON challenges(status);
CREATE INDEX idx_challenges_expires_at ON challenges(expires_at);

-- ============================================================
-- TEAMS TABLE
-- ============================================================

CREATE TABLE teams (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(255) NOT NULL UNIQUE,
  description TEXT,
  logo_url VARCHAR(500),
  
  -- Team Leadership
  captain_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  
  -- Team Statistics
  wins INTEGER NOT NULL DEFAULT 0,
  losses INTEGER NOT NULL DEFAULT 0,
  members_count INTEGER NOT NULL DEFAULT 1,
  
  -- Metadata
  location VARCHAR(255),
  website VARCHAR(500),
  is_public BOOLEAN NOT NULL DEFAULT true,
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  -- Constraints
  CONSTRAINT team_stats_non_negative CHECK (wins >= 0 AND losses >= 0 AND members_count > 0)
);

CREATE INDEX idx_teams_captain ON teams(captain_id);
CREATE INDEX idx_teams_name ON teams(name);
CREATE INDEX idx_teams_is_public ON teams(is_public);
CREATE INDEX idx_teams_wins ON teams(wins DESC);

-- ============================================================
-- TEAM MEMBERS TABLE
-- ============================================================

CREATE TABLE team_members (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Role in Team
  role VARCHAR(50) NOT NULL DEFAULT 'member' CHECK (role IN ('captain', 'vice_captain', 'member')),
  
  -- Member Status
  status VARCHAR(50) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'suspended')),
  
  -- Timestamps
  joined_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  left_at TIMESTAMP WITH TIME ZONE,
  
  -- Unique constraint: Each user can be in a team only once
  UNIQUE (team_id, user_id)
);

CREATE INDEX idx_team_members_team ON team_members(team_id);
CREATE INDEX idx_team_members_user ON team_members(user_id);
CREATE INDEX idx_team_members_role ON team_members(role);
CREATE INDEX idx_team_members_status ON team_members(status);

-- ============================================================
-- TOURNAMENTS TABLE
-- ============================================================

CREATE TABLE tournaments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(255) NOT NULL,
  description TEXT,
  
  -- Tournament Details
  start_date TIMESTAMP WITH TIME ZONE NOT NULL,
  end_date TIMESTAMP WITH TIME ZONE NOT NULL,
  registration_deadline TIMESTAMP WITH TIME ZONE,
  
  structure tournament_structure NOT NULL DEFAULT 'single_elimination',
  status tournament_status NOT NULL DEFAULT 'draft',
  
  -- Participants
  max_participants INTEGER NOT NULL CHECK (max_participants > 0),
  current_participants_count INTEGER NOT NULL DEFAULT 0,
  
  -- Metadata
  location VARCHAR(255),
  prize_pool DECIMAL(12, 2) DEFAULT 0.00,
  organizer_id UUID NOT NULL REFERENCES users(id) ON DELETE SET NULL,
  
  -- Settings
  is_public BOOLEAN NOT NULL DEFAULT true,
  requires_approval BOOLEAN NOT NULL DEFAULT false,
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  -- Constraints
  CONSTRAINT tournament_dates CHECK (start_date < end_date),
  CONSTRAINT prize_pool_non_negative CHECK (prize_pool >= 0)
);

CREATE INDEX idx_tournaments_organizer ON tournaments(organizer_id);
CREATE INDEX idx_tournaments_status ON tournaments(status);
CREATE INDEX idx_tournaments_start_date ON tournaments(start_date);
CREATE INDEX idx_tournaments_is_public ON tournaments(is_public);

-- ============================================================
-- TOURNAMENT PARTICIPANTS TABLE
-- ============================================================

CREATE TABLE tournament_participants (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tournament_id UUID NOT NULL REFERENCES tournaments(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Registration Status
  status VARCHAR(50) NOT NULL DEFAULT 'registered' CHECK (status IN ('registered', 'confirmed', 'withdrew', 'disqualified')),
  seed_position INTEGER,
  
  -- Timestamps
  registered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  confirmed_at TIMESTAMP WITH TIME ZONE,
  
  -- Unique constraint: Each user participates once per tournament
  UNIQUE (tournament_id, user_id),
  
  -- Constraint: Seed position within participant count
  CONSTRAINT valid_seed CHECK (seed_position IS NULL OR seed_position > 0)
);

CREATE INDEX idx_tournament_participants_tournament ON tournament_participants(tournament_id);
CREATE INDEX idx_tournament_participants_user ON tournament_participants(user_id);
CREATE INDEX idx_tournament_participants_status ON tournament_participants(status);

-- ============================================================
-- TOURNAMENT MATCHES TABLE
-- ============================================================

CREATE TABLE tournament_matches (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tournament_id UUID NOT NULL REFERENCES tournaments(id) ON DELETE CASCADE,
  match_id UUID NOT NULL REFERENCES matches(id) ON DELETE CASCADE,
  
  -- Tournament Structure
  round_number INTEGER NOT NULL CHECK (round_number > 0),
  match_position INTEGER NOT NULL CHECK (match_position > 0),
  
  -- Bracket Information
  bracket_position VARCHAR(50), -- e.g., "A1", "B2", etc.
  
  -- Advancement
  winner_advances_to_match_id UUID REFERENCES tournament_matches(id) ON DELETE SET NULL,
  loser_advances_to_match_id UUID REFERENCES tournament_matches(id) ON DELETE SET NULL,
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tournament_matches_tournament ON tournament_matches(tournament_id);
CREATE INDEX idx_tournament_matches_match ON tournament_matches(match_id);
CREATE INDEX idx_tournament_matches_round ON tournament_matches(tournament_id, round_number);

-- ============================================================
-- BETS TABLE
-- ============================================================

CREATE TABLE bets (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  bettor_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Bet Target (either match or tournament)
  match_id UUID REFERENCES matches(id) ON DELETE CASCADE,
  tournament_id UUID REFERENCES tournaments(id) ON DELETE CASCADE,
  
  -- Bet Details
  prediction VARCHAR(255) NOT NULL, -- e.g., "player1_wins", "player2_wins", "2-1"
  amount DECIMAL(12, 2) NOT NULL CHECK (amount > 0),
  odds DECIMAL(5, 3) NOT NULL CHECK (odds > 0),
  potential_return DECIMAL(12, 2) NOT NULL,
  
  -- Bet Status
  status bet_status NOT NULL DEFAULT 'pending',
  result_amount DECIMAL(12, 2), -- Actual amount won/lost
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  settled_at TIMESTAMP WITH TIME ZONE,
  
  -- Constraints
  CONSTRAINT bet_has_target CHECK (match_id IS NOT NULL OR tournament_id IS NOT NULL),
  CONSTRAINT bet_targets_mutually_exclusive CHECK (
    (match_id IS NOT NULL AND tournament_id IS NULL) OR
    (match_id IS NULL AND tournament_id IS NOT NULL)
  ),
  CONSTRAINT result_only_when_settled CHECK (
    (status = 'pending' AND result_amount IS NULL) OR
    (status != 'pending' AND result_amount IS NOT NULL)
  )
);

CREATE INDEX idx_bets_bettor ON bets(bettor_id);
CREATE INDEX idx_bets_match ON bets(match_id);
CREATE INDEX idx_bets_tournament ON bets(tournament_id);
CREATE INDEX idx_bets_status ON bets(status);
CREATE INDEX idx_bets_created_at ON bets(created_at);

-- ============================================================
-- POSTS TABLE (Social Feed)
-- ============================================================

CREATE TABLE posts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  author_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Content
  content TEXT NOT NULL,
  media_urls TEXT[], -- Array of URLs for images/videos
  
  -- Engagement
  likes_count INTEGER NOT NULL DEFAULT 0 CHECK (likes_count >= 0),
  comments_count INTEGER NOT NULL DEFAULT 0 CHECK (comments_count >= 0),
  shares_count INTEGER NOT NULL DEFAULT 0 CHECK (shares_count >= 0),
  
  -- Privacy
  visibility post_visibility NOT NULL DEFAULT 'public',
  
  -- Optional: Link to match or tournament
  match_id UUID REFERENCES matches(id) ON DELETE SET NULL,
  tournament_id UUID REFERENCES tournaments(id) ON DELETE SET NULL,
  
  -- Moderation
  is_pinned BOOLEAN NOT NULL DEFAULT false,
  is_archived BOOLEAN NOT NULL DEFAULT false,
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  -- Constraints
  CONSTRAINT post_content_not_empty CHECK (LENGTH(TRIM(content)) > 0)
);

CREATE INDEX idx_posts_author ON posts(author_id);
CREATE INDEX idx_posts_visibility ON posts(visibility);
CREATE INDEX idx_posts_created_at ON posts(created_at DESC);
CREATE INDEX idx_posts_match ON posts(match_id);
CREATE INDEX idx_posts_tournament ON posts(tournament_id);
CREATE INDEX idx_posts_is_pinned ON posts(is_pinned) WHERE is_pinned = true;

-- ============================================================
-- POST LIKES TABLE
-- ============================================================

CREATE TABLE post_likes (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  post_id UUID NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  -- Each user can like a post only once
  UNIQUE (post_id, user_id)
);

CREATE INDEX idx_post_likes_post ON post_likes(post_id);
CREATE INDEX idx_post_likes_user ON post_likes(user_id);

-- ============================================================
-- COMMENTS TABLE
-- ============================================================

CREATE TABLE comments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  post_id UUID NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  author_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Nested comments support
  parent_comment_id UUID REFERENCES comments(id) ON DELETE CASCADE,
  
  -- Content
  content TEXT NOT NULL,
  
  -- Engagement
  likes_count INTEGER NOT NULL DEFAULT 0 CHECK (likes_count >= 0),
  
  -- Moderation
  is_deleted BOOLEAN NOT NULL DEFAULT false,
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  -- Constraints
  CONSTRAINT comment_content_not_empty CHECK (LENGTH(TRIM(content)) > 0)
);

CREATE INDEX idx_comments_post ON comments(post_id);
CREATE INDEX idx_comments_author ON comments(author_id);
CREATE INDEX idx_comments_parent ON comments(parent_comment_id);
CREATE INDEX idx_comments_created_at ON comments(created_at);

-- ============================================================
-- COMMENT LIKES TABLE
-- ============================================================

CREATE TABLE comment_likes (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  comment_id UUID NOT NULL REFERENCES comments(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  -- Each user can like a comment only once
  UNIQUE (comment_id, user_id)
);

CREATE INDEX idx_comment_likes_comment ON comment_likes(comment_id);
CREATE INDEX idx_comment_likes_user ON comment_likes(user_id);

-- ============================================================
-- NOTIFICATIONS TABLE
-- ============================================================

CREATE TABLE notifications (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Notification Details
  type notification_type NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT,
  
  -- Related Entity (polymorphic relationship)
  related_entity_type VARCHAR(50), -- 'match', 'challenge', 'team', 'post', etc.
  related_entity_id UUID,
  
  -- Actor (who triggered the notification)
  actor_id UUID REFERENCES users(id) ON DELETE SET NULL,
  
  -- Notification Status
  is_read BOOLEAN NOT NULL DEFAULT false,
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at TIMESTAMP WITH TIME ZONE,
  
  -- Auto-expire notifications after 90 days
  expires_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP + INTERVAL '90 days'
);

CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_type ON notifications(type);
CREATE INDEX idx_notifications_is_read ON notifications(user_id, is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at DESC);
CREATE INDEX idx_notifications_expires_at ON notifications(expires_at);

-- ============================================================
-- FRIENDSHIPS TABLE (Social Connections)
-- ============================================================

CREATE TABLE friendships (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id_1 UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  user_id_2 UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Friendship Status
  status VARCHAR(50) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'accepted', 'blocked')),
  
  -- For friend requests, track who initiated
  initiated_by_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Timestamps
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  accepted_at TIMESTAMP WITH TIME ZONE,
  
  -- Constraints
  CONSTRAINT users_must_differ CHECK (user_id_1 != user_id_2),
  CONSTRAINT users_ordered CHECK (user_id_1 < user_id_2),
  CONSTRAINT accepted_only_after_created CHECK (accepted_at IS NULL OR accepted_at >= created_at)
);

CREATE INDEX idx_friendships_user1 ON friendships(user_id_1);
CREATE INDEX idx_friendships_user2 ON friendships(user_id_2);
CREATE INDEX idx_friendships_status ON friendships(status);

-- ============================================================
-- USER RATINGS HISTORY TABLE (Optional but useful)
-- ============================================================

CREATE TABLE user_rating_history (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Rating Change
  old_rating DECIMAL(5, 2) NOT NULL,
  new_rating DECIMAL(5, 2) NOT NULL,
  change_amount DECIMAL(5, 2) NOT NULL,
  
  -- Related Match
  match_id UUID REFERENCES matches(id) ON DELETE SET NULL,
  
  -- Reason
  reason VARCHAR(100), -- 'match_win', 'match_loss', 'tournament_placement', 'rating_adjustment'
  
  -- Timestamp
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_rating_history_user ON user_rating_history(user_id);
CREATE INDEX idx_user_rating_history_match ON user_rating_history(match_id);
CREATE INDEX idx_user_rating_history_created_at ON user_rating_history(created_at DESC);

-- ============================================================
-- TRANSACTION/ACTIVITY AUDIT LOG (For betting/financial tracking)
-- ============================================================

CREATE TABLE activity_log (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Activity Details
  action VARCHAR(100) NOT NULL, -- 'bet_placed', 'bet_won', 'bet_lost', 'match_created', etc.
  entity_type VARCHAR(50), -- 'bet', 'match', 'challenge', 'post', etc.
  entity_id UUID,
  
  -- Amount involved (if applicable)
  amount DECIMAL(12, 2),
  
  -- Description
  description TEXT,
  
  -- IP Address (for security)
  ip_address INET,
  
  -- Timestamp
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_activity_log_user ON activity_log(user_id);
CREATE INDEX idx_activity_log_action ON activity_log(action);
CREATE INDEX idx_activity_log_created_at ON activity_log(created_at DESC);

-- ============================================================
-- MATERIALIZED VIEWS FOR COMMON QUERIES
-- ============================================================

-- User leaderboard view
CREATE VIEW user_leaderboard AS
SELECT 
  u.id,
  u.username,
  u.avatar_url,
  u.rating,
  u.wins,
  u.losses,
  ROUND((u.wins::FLOAT / NULLIF(u.wins + u.losses, 0)) * 100, 2) as win_rate,
  u.followers_count,
  u.created_at,
  ROW_NUMBER() OVER (ORDER BY u.rating DESC) as rank
FROM users u
WHERE u.is_active = true
ORDER BY u.rating DESC;

-- Active matches view
CREATE VIEW active_matches AS
SELECT 
  m.id,
  m.title,
  u1.username as player1_name,
  u2.username as player2_name,
  m.match_date,
  m.location,
  m.status,
  m.created_at
FROM matches m
JOIN users u1 ON m.player1_id = u1.id
JOIN users u2 ON m.player2_id = u2.id
WHERE m.status IN ('scheduled', 'ongoing')
ORDER BY m.match_date ASC;

-- Team statistics view
CREATE VIEW team_statistics AS
SELECT 
  t.id,
  t.name,
  t.captain_id,
  COUNT(DISTINCT tm.user_id) as member_count,
  t.wins,
  t.losses,
  ROUND((t.wins::FLOAT / NULLIF(t.wins + t.losses, 0)) * 100, 2) as win_rate
FROM teams t
LEFT JOIN team_members tm ON t.id = tm.team_id
GROUP BY t.id, t.name, t.captain_id, t.wins, t.losses;

-- ============================================================
-- FUNCTIONS FOR COMMON OPERATIONS
-- ============================================================

-- Function to update post comments count
CREATE OR REPLACE FUNCTION update_post_comments_count()
RETURNS TRIGGER AS $$
BEGIN
  IF TG_OP = 'INSERT' THEN
    UPDATE posts SET comments_count = comments_count + 1 WHERE id = NEW.post_id;
    RETURN NEW;
  ELSIF TG_OP = 'DELETE' THEN
    UPDATE posts SET comments_count = comments_count - 1 WHERE id = OLD.post_id;
    RETURN OLD;
  END IF;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Trigger for comments count
CREATE TRIGGER tr_update_post_comments_count
AFTER INSERT OR DELETE ON comments
FOR EACH ROW
EXECUTE FUNCTION update_post_comments_count();

-- Function to update post likes count
CREATE OR REPLACE FUNCTION update_post_likes_count()
RETURNS TRIGGER AS $$
BEGIN
  IF TG_OP = 'INSERT' THEN
    UPDATE posts SET likes_count = likes_count + 1 WHERE id = NEW.post_id;
    RETURN NEW;
  ELSIF TG_OP = 'DELETE' THEN
    UPDATE posts SET likes_count = likes_count - 1 WHERE id = OLD.post_id;
    RETURN OLD;
  END IF;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Trigger for post likes count
CREATE TRIGGER tr_update_post_likes_count
AFTER INSERT OR DELETE ON post_likes
FOR EACH ROW
EXECUTE FUNCTION update_post_likes_count();

-- Function to update comment likes count
CREATE OR REPLACE FUNCTION update_comment_likes_count()
RETURNS TRIGGER AS $$
BEGIN
  IF TG_OP = 'INSERT' THEN
    UPDATE comments SET likes_count = likes_count + 1 WHERE id = NEW.comment_id;
    RETURN NEW;
  ELSIF TG_OP = 'DELETE' THEN
    UPDATE comments SET likes_count = likes_count - 1 WHERE id = OLD.comment_id;
    RETURN OLD;
  END IF;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Trigger for comment likes count
CREATE TRIGGER tr_update_comment_likes_count
AFTER INSERT OR DELETE ON comment_likes
FOR EACH ROW
EXECUTE FUNCTION update_comment_likes_count();

-- Function to update user updated_at timestamp
CREATE OR REPLACE FUNCTION update_user_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger for users timestamp
CREATE TRIGGER tr_update_user_timestamp
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION update_user_timestamp();

-- Function to update match timestamp
CREATE OR REPLACE FUNCTION update_match_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger for matches timestamp
CREATE TRIGGER tr_update_match_timestamp
BEFORE UPDATE ON matches
FOR EACH ROW
EXECUTE FUNCTION update_match_timestamp();

-- ============================================================
-- DATABASE PERMISSIONS (Example - Adjust to your needs)
-- ============================================================

-- Create application role
-- CREATE ROLE badnet_app WITH PASSWORD 'your_secure_password';

-- Grant permissions
-- GRANT CONNECT ON DATABASE badnet_db TO badnet_app;
-- GRANT USAGE ON SCHEMA public TO badnet_app;
-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO badnet_app;
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO badnet_app;
-- GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO badnet_app;

-- ============================================================
-- END OF SCHEMA
-- ============================================================
