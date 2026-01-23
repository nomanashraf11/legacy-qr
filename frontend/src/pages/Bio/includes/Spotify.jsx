import { useEffect, useState } from "react";
import SpotifyWebApi from "spotify-web-api-js";
import { SPOTIFY_ACCESS_TOKEN } from "../../../config";

const spotifyApi = new SpotifyWebApi();
spotifyApi.setAccessToken(SPOTIFY_ACCESS_TOKEN);

const useScript = (url) => {
  const [isScriptLoaded, setIsScriptLoaded] = useState(false);

  useEffect(() => {
    const script = document.createElement("script");
    script.src = url;
    script.async = true;

    script.onload = () => {
      setIsScriptLoaded(true);
    };

    document.body.appendChild(script);

    return () => {
      document.body.removeChild(script);
    };
  }, [url]);

  return isScriptLoaded;
};

export const SpotifyPlayer = () => {
  const isScriptLoaded = useScript("https://sdk.scdn.co/spotify-player.js");

  useEffect(() => {
    if (!isScriptLoaded) return;

    if (isScriptLoaded) {
      window.onSpotifyWebPlaybackSDKReady = () => {
        const token = SPOTIFY_ACCESS_TOKEN;
        const player = new window.Spotify.Player({
          name: "My Web Playback SDK Player",
          getOAuthToken: (cb) => {
            cb(token);
          },
          //   volume: 0.5, // Example volume setting
        });

        // Connect to the player!
        player.connect().then((success) => {
          if (success) {
            console.log(
              "The Web Playback SDK successfully connected to Spotify!"
            );
          }
        });

        // Event listeners and player methods can be added here
        player.on("ready", (data) => {
          console.log("Web Playback SDK Player is ready to play music!", data);
        });

        player.on("player_state_changed", (state) => {
          console.log("Player state has changed!", state);
        });

        // Add more event listeners and player methods as needed
      };
    }
  }, [isScriptLoaded]);

  return <div>Spotify Player</div>;
};
