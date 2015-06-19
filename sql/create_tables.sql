CREATE SCHEMA minigame;

-- Table player
CREATE TABLE minigame.player (
    id         serial                NOT NULL,
    name       character varying(45) NOT NULL,
    CONSTRAINT player_pkey PRIMARY KEY (id),
    CONSTRAINT player_name UNIQUE (name)
);

-- Table hangman
CREATE TABLE minigame.hangman (
    id                 serial                NOT NULL,
    word               character varying(45) NOT NULL,
    letters_played     text                  NOT NULL,
    bad_letters_played text                  NOT NULL,
    remaining_chances  text                  NOT NULL,
    next_player_id     int                   NULL,
    CONSTRAINT hangman_pkey           PRIMARY KEY (id),
    CONSTRAINT hangman_player_fkey FOREIGN KEY (next_player_id) REFERENCES minigame.player (id)
);

-- Table hangman_has_player
CREATE TABLE minigame.hangman_has_player (
    hangman_id int NOT NULL,
    player_id  int NOT NULL,
    CONSTRAINT hangman_has_player_pkey PRIMARY KEY (hangman_id, player_id),
    CONSTRAINT hhp_player_fkey         FOREIGN KEY (player_id) REFERENCES minigame.player (id),
    CONSTRAINT hhp_hangman_fkey        FOREIGN KEY (hangman_id) REFERENCES minigame.hangman (id)
);