-- ============================================================
-- BadNet Database Migrations
-- PostgreSQL Database Setup Instructions
-- ============================================================

-- ============================================================
-- MIGRATION 001: Create ENUM Types
-- Description: Create custom PostgreSQL enum types
-- Status: Up
-- ============================================================

CREATE TYPE user_role AS ENUM ('user', 'admin', 'moderator');
CREATE TYPE match_status AS ENUM ('scheduled', 'ongoing', 'completed', 'cancelled', 'postponed');
CREATE TYPE challenge_status AS ENUM ('pending', 'accepted', 'declined', 'completed');
CREATE TYPE tournament_status AS ENUM ('draft', 'registration', 'ongoing', 'completed', 'cancelled');
CREATE TYPE tournament_structure AS ENUM ('single_elimination', 'double_elimination', 'round_robin', 'group_stage');
CREATE TYPE bet_status AS ENUM ('pending', 'won', 'lost', 'cancelled', 'refunded');
CREATE TYPE post_visibility AS ENUM ('public', 'friends', 'private');
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
-- MIGRATION 002: Create USERS Table
-- Description: Core user accounts and profiles
-- Status: Up
-- ============================================================

CREATE TABLE users (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  email VARCHAR(255) NOT NULL UNIQUE,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  bio TEXT,
  avatar_url VARCHAR(500),
  phone VARCHAR(20),
  date_of_birth DATE,
  location VARCHAR(255),
  is_active BOOLEAN NOT NULL DEFAULT true,
  is_verified BOOLEAN NOT NULL DEFAULT false,
  role user_role NOT NULL DEFAULT 'user',
  rating DECIMAL(5, 2) NOT NULL DEFAULT 1200.00,
  wins INTEGER NOT NULL DEFAULT 0,
  losses INTEGER NOT NULL DEFAULT 0,
  followers_count INTEGER NOT NULL DEFAULT 0,
  following_count INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at TIMESTAMP WITH TIME ZONE,
  CONSTRAINT users_email_format CHECK (email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$'),
  CONSTRAINT users_rating_range CHECK (rating >= 0),
  CONSTRAINT users_stats_non_negative CHECK (wins >= 0 AND losses >= 0)
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_is_active ON users(is_active);
CREATE INDEX idx_users_rating ON users(rating DESC);

-- ============================================================
-- MIGRATION 003: Create MATCHES Table
-- Description: Badminton match records
-- Status: Up
-- ============================================================

CREATE TABLE matches (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  title VARCHAR(255) NOT NULL,
  description TEXT,
  player1_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  player2_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  location VARCHAR(255) NOT NULL,
  match_date TIMESTAMP WITH TIME ZONE NOT NULL,
  duration_minutes INTEGER,
  player1_score INTEGER CHECK (player1_score >= 0),
  player2_score INTEGER CHECK (player2_score >= 0),
  status match_status NOT NULL DEFAULT 'scheduled',
  best_of_x INTEGER NOT NULL DEFAULT 3 CHECK (best_of_x IN (1, 3, 5)),
  created_by_id UUID NOT NULL REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
-- MIGRATION 004: Create CHALLENGES Table
-- Description: Player-to-player challenges
-- Status: Up
-- ============================================================

CREATE TABLE challenges (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  challenger_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  opponent_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  title VARCHAR(255),
  message TEXT,
  status challenge_status NOT NULL DEFAULT 'pending',
  match_id UUID REFERENCES matches(id) ON DELETE SET NULL,
  expires_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP + INTERVAL '30 days',
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  accepted_at TIMESTAMP WITH TIME ZONE,
  declined_at TIMESTAMP WITH TIME ZONE,
  CONSTRAINT challenger_must_differ CHECK (challenger_id != opponent_id)
);

CREATE INDEX idx_challenges_challenger ON challenges(challenger_id);
CREATE INDEX idx_challenges_opponent ON challenges(opponent_id);
CREATE INDEX idx_challenges_status ON challenges(status);
CREATE INDEX idx_challenges_expires_at ON challenges(expires_at);

-- ============================================================
-- MIGRATION 005: Create TEAMS Table
-- Description: Team groups and information
-- Status: Up
-- ============================================================

CREATE TABLE teams (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(255) NOT NULL UNIQUE,
  description TEXT,
  logo_url VARCHAR(500),
  captain_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  wins INTEGER NOT NULL DEFAULT 0,
  losses INTEGER NOT NULL DEFAULT 0,
  members_count INTEGER NOT NULL DEFAULT 1,
  location VARCHAR(255),
  website VARCHAR(500),
  is_public BOOLEAN NOT NULL DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT team_stats_non_negative CHECK (wins >= 0 AND losses >= 0 AND members_count > 0)
);

CREATE INDEX idx_teams_captain ON teams(captain_id);
CREATE INDEX idx_teams_name ON teams(name);
CREATE INDEX idx_teams_is_public ON teams(is_public);
CREATE INDEX idx_teams_wins ON teams(wins DESC);

-- ============================================================
-- MIGRATION 006: Create TEAM_MEMBERS Table
-- Description: Team membership and roles
-- Status: Up
-- ============================================================

CREATE TABLE team_members (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  role VARCHAR(50) NOT NULL DEFAULT 'member' CHECK (role IN ('captain', 'vice_captain', 'member')),
  status VARCHAR(50) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'suspended')),
  joined_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  left_at TIMESTAMP WITH TIME ZONE,
  UNIQUE (team_id, user_id)
);

CREATE INDEX idx_team_members_team ON team_members(team_id);
CREATE INDEX idx_team_members_user ON team_members(user_id);
CREATE INDEX idx_team_members_role ON team_members(role);
CREATE INDEX idx_team_members_status ON team_members(status);

-- ============================================================
-- MIGRATION 007: Create TOURNAMENTS Table
-- Description: Large organized tournaments
-- Status: Up
-- ============================================================

CREATE TABLE tournaments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(255) NOT NULL,
  description TEXT,
  start_date TIMESTAMP WITH TIME ZONE NOT NULL,
  end_date TIMESTAMP WITH TIME ZONE NOT NULL,
  registration_deadline TIMESTAMP WITH TIME ZONE,
  structure tournament_structure NOT NULL DEFAULT 'single_elimination',
  status tournament_status NOT NULL DEFAULT 'draft',
  max_participants INTEGER NOT NULL CHECK (max_participants > 0),
  current_participants_count INTEGER NOT NULL DEFAULT 0,
  location VARCHAR(255),
  prize_pool DECIMAL(12, 2) DEFAULT 0.00,
  organizer_id UUID NOT NULL REFERENCES users(id) ON DELETE SET NULL,
  is_public BOOLEAN NOT NULL DEFAULT true,
  requires_approval BOOLEAN NOT NULL DEFAULT false,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT tournament_dates CHECK (start_date < end_date),
  CONSTRAINT prize_pool_non_negative CHECK (prize_pool >= 0)
);

CREATE INDEX idx_tournaments_organizer ON tournaments(organizer_id);
CREATE INDEX idx_tournaments_status ON tournaments(status);
CREATE INDEX idx_tournaments_start_date ON tournaments(start_date);
CREATE INDEX idx_tournaments_is_public ON tournaments(is_public);

-- ============================================================
-- MIGRATION 008: Create TOURNAMENT_PARTICIPANTS Table
-- Description: Tournament registration
-- Status: Up
-- ============================================================

CREATE TABLE tournament_participants (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tournament_id UUID NOT NULL REFERENCES tournaments(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  status VARCHAR(50) NOT NULL DEFAULT 'registered' CHECK (status IN ('registered', 'confirmed', 'withdrew', 'disqualified')),
  seed_position INTEGER,
  registered_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  confirmed_at TIMESTAMP WITH TIME ZONE,
  UNIQUE (tournament_id, user_id),
  CONSTRAINT valid_seed CHECK (seed_position IS NULL OR seed_position > 0)
);

CREATE INDEX idx_tournament_participants_tournament ON tournament_participants(tournament_id);
CREATE INDEX idx_tournament_participants_user ON tournament_participants(user_id);
CREATE INDEX idx_tournament_participants_status ON tournament_participants(status);

-- ============================================================
-- MIGRATION 009: Create TOURNAMENT_MATCHES Table
-- Description: Matches within tournaments
-- Status: Up
-- ============================================================

CREATE TABLE tournament_matches (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tournament_id UUID NOT NULL REFERENCES tournaments(id) ON DELETE CASCADE,
  match_id UUID NOT NULL REFERENCES matches(id) ON DELETE CASCADE,
  round_number INTEGER NOT NULL CHECK (round_number > 0),
  match_position INTEGER NOT NULL CHECK (match_position > 0),
  bracket_position VARCHAR(50),
  winner_advances_to_match_id UUID REFERENCES tournament_matches(id) ON DELETE SET NULL,
  loser_advances_to_match_id UUID REFERENCES tournament_matches(id) ON DELETE SET NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tournament_matches_tournament ON tournament_matches(tournament_id);
CREATE INDEX idx_tournament_matches_match ON tournament_matches(match_id);
CREATE INDEX idx_tournament_matches_round ON tournament_matches(tournament_id, round_number);

-- ============================================================
-- MIGRATION 010: Create BETS Table
-- Description: Betting and predictions
-- Status: Up
-- ============================================================

CREATE TABLE bets (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  bettor_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  match_id UUID REFERENCES matches(id) ON DELETE CASCADE,
  tournament_id UUID REFERENCES tournaments(id) ON DELETE CASCADE,
  prediction VARCHAR(255) NOT NULL,
  amount DECIMAL(12, 2) NOT NULL CHECK (amount > 0),
  odds DECIMAL(5, 3) NOT NULL CHECK (odds > 0),
  potential_return DECIMAL(12, 2) NOT NULL,
  status bet_status NOT NULL DEFAULT 'pending',
  result_amount DECIMAL(12, 2),
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  settled_at TIMESTAMP WITH TIME ZONE,
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
-- MIGRATION 011: Create POSTS Table
-- Description: Social media posts
-- Status: Up
-- ============================================================

CREATE TABLE posts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  author_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  content TEXT NOT NULL,
  media_urls TEXT[],
  likes_count INTEGER NOT NULL DEFAULT 0 CHECK (likes_count >= 0),
  comments_count INTEGER NOT NULL DEFAULT 0 CHECK (comments_count >= 0),
  shares_count INTEGER NOT NULL DEFAULT 0 CHECK (shares_count >= 0),
  visibility post_visibility NOT NULL DEFAULT 'public',
  match_id UUID REFERENCES matches(id) ON DELETE SET NULL,
  tournament_id UUID REFERENCES tournaments(id) ON DELETE SET NULL,
  is_pinned BOOLEAN NOT NULL DEFAULT false,
  is_archived BOOLEAN NOT NULL DEFAULT false,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT post_content_not_empty CHECK (LENGTH(TRIM(content)) > 0)
);

CREATE INDEX idx_posts_author ON posts(author_id);
CREATE INDEX idx_posts_visibility ON posts(visibility);
CREATE INDEX idx_posts_created_at ON posts(created_at DESC);
CREATE INDEX idx_posts_match ON posts(match_id);
CREATE INDEX idx_posts_tournament ON posts(tournament_id);
CREATE INDEX idx_posts_is_pinned ON posts(is_pinned) WHERE is_pinned = true;

-- ============================================================
-- MIGRATION 012: Create POST_LIKES Table
-- Description: User likes on posts
-- Status: Up
-- ============================================================

CREATE TABLE post_likes (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  post_id UUID NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (post_id, user_id)
);

CREATE INDEX idx_post_likes_post ON post_likes(post_id);
CREATE INDEX idx_post_likes_user ON post_likes(user_id);

-- ============================================================
-- MIGRATION 013: Create COMMENTS Table
-- Description: Comments on posts (supports nested replies)
-- Status: Up
-- ============================================================

CREATE TABLE comments (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  post_id UUID NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  author_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  parent_comment_id UUID REFERENCES comments(id) ON DELETE CASCADE,
  content TEXT NOT NULL,
  likes_count INTEGER NOT NULL DEFAULT 0 CHECK (likes_count >= 0),
  is_deleted BOOLEAN NOT NULL DEFAULT false,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT comment_content_not_empty CHECK (LENGTH(TRIM(content)) > 0)
);

CREATE INDEX idx_comments_post ON comments(post_id);
CREATE INDEX idx_comments_author ON comments(author_id);
CREATE INDEX idx_comments_parent ON comments(parent_comment_id);
CREATE INDEX idx_comments_created_at ON comments(created_at);

-- ============================================================
-- MIGRATION 014: Create COMMENT_LIKES Table
-- Description: User likes on comments
-- Status: Up
-- ============================================================

CREATE TABLE comment_likes (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  comment_id UUID NOT NULL REFERENCES comments(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (comment_id, user_id)
);

CREATE INDEX idx_comment_likes_comment ON comment_likes(comment_id);
CREATE INDEX idx_comment_likes_user ON comment_likes(user_id);

-- ============================================================
-- MIGRATION 015: Create NOTIFICATIONS Table
-- Description: User notifications for events
-- Status: Up
-- ============================================================

CREATE TABLE notifications (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  type notification_type NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT,
  related_entity_type VARCHAR(50),
  related_entity_id UUID,
  actor_id UUID REFERENCES users(id) ON DELETE SET NULL,
  is_read BOOLEAN NOT NULL DEFAULT false,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at TIMESTAMP WITH TIME ZONE,
  expires_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP + INTERVAL '90 days'
);

CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_type ON notifications(type);
CREATE INDEX idx_notifications_is_read ON notifications(user_id, is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at DESC);
CREATE INDEX idx_notifications_expires_at ON notifications(expires_at);

-- ============================================================
-- MIGRATION 016: Create FRIENDSHIPS Table
-- Description: Social connections and friend requests
-- Status: Up
-- ============================================================

CREATE TABLE friendships (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id_1 UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  user_id_2 UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  status VARCHAR(50) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'accepted', 'blocked')),
  initiated_by_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  accepted_at TIMESTAMP WITH TIME ZONE,
  CONSTRAINT users_must_differ CHECK (user_id_1 != user_id_2),
  CONSTRAINT users_ordered CHECK (user_id_1 < user_id_2),
  CONSTRAINT accepted_only_after_created CHECK (accepted_at IS NULL OR accepted_at >= created_at)
);

CREATE INDEX idx_friendships_user1 ON friendships(user_id_1);
CREATE INDEX idx_friendships_user2 ON friendships(user_id_2);
CREATE INDEX idx_friendships_status ON friendships(status);

-- ============================================================
-- MIGRATION 017: Create USER_RATING_HISTORY Table
-- Description: Track rating changes over time (for Elo system)
-- Status: Up
-- ============================================================

CREATE TABLE user_rating_history (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  old_rating DECIMAL(5, 2) NOT NULL,
  new_rating DECIMAL(5, 2) NOT NULL,
  change_amount DECIMAL(5, 2) NOT NULL,
  match_id UUID REFERENCES matches(id) ON DELETE SET NULL,
  reason VARCHAR(100),
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_rating_history_user ON user_rating_history(user_id);
CREATE INDEX idx_user_rating_history_match ON user_rating_history(match_id);
CREATE INDEX idx_user_rating_history_created_at ON user_rating_history(created_at DESC);

-- ============================================================
-- MIGRATION 018: Create ACTIVITY_LOG Table
-- Description: Audit trail for important actions
-- Status: Up
-- ============================================================

CREATE TABLE activity_log (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50),
  entity_id UUID,
  amount DECIMAL(12, 2),
  description TEXT,
  ip_address INET,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_activity_log_user ON activity_log(user_id);
CREATE INDEX idx_activity_log_action ON activity_log(action);
CREATE INDEX idx_activity_log_created_at ON activity_log(created_at DESC);

-- ============================================================
-- MIGRATION 019: Create Views
-- Description: Materialized views for common queries
-- Status: Up
-- ============================================================

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
-- MIGRATION 020: Create Triggers and Functions
-- Description: Auto-update counts and timestamps
-- Status: Up
-- ============================================================

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

CREATE TRIGGER tr_update_post_comments_count
AFTER INSERT OR DELETE ON comments
FOR EACH ROW
EXECUTE FUNCTION update_post_comments_count();

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

CREATE TRIGGER tr_update_post_likes_count
AFTER INSERT OR DELETE ON post_likes
FOR EACH ROW
EXECUTE FUNCTION update_post_likes_count();

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

CREATE TRIGGER tr_update_comment_likes_count
AFTER INSERT OR DELETE ON comment_likes
FOR EACH ROW
EXECUTE FUNCTION update_comment_likes_count();

CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tr_update_user_timestamp
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER tr_update_match_timestamp
BEFORE UPDATE ON matches
FOR EACH ROW
EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER tr_update_challenge_timestamp
BEFORE UPDATE ON challenges
FOR EACH ROW
EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER tr_update_post_timestamp
BEFORE UPDATE ON posts
FOR EACH ROW
EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER tr_update_comment_timestamp
BEFORE UPDATE ON comments
FOR EACH ROW
EXECUTE FUNCTION update_timestamp();

-- ============================================================
-- MIGRATION COMPLETE
-- All 20 migrations have been applied successfully
-- Database is ready for BadNet application
-- ============================================================
