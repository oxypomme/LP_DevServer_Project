import { StatusCodes } from "http-status-codes";
import { map, Map, marker, Marker, tileLayer } from "leaflet";
import { fetchAPI } from "./api";
import { ILocation, IUser } from "./types/responses";

let mymap: Map | null = null;
const markers: { [user_id: string]: Marker } = {};

// TODO: Rayon ?

(async () => {
  const { status: authStatus } = await fetchAPI("GET /auth");

  if (document.getElementById("mapid")) {
    navigator.geolocation.getCurrentPosition(
      ({ coords }) => {
        const latLng = {
          lat: coords.latitude,
          lng: coords.longitude,
        };
        mymap = map("mapid").setView(latLng, 13);
        tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png", {
          attribution:
            '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(mymap);
      },
      (error) => {
        console.error("[MAP]", error);
      }
    );
  }

  if (authStatus === StatusCodes.OK) {
    const { status, payload: user } = await fetchAPI<IUser>(
      "GET /api/users/me"
    );
    if (status !== StatusCodes.OK || typeof user === "string") {
      console.error("[MSG]", user);
      return;
    }

    navigator.geolocation.watchPosition(
      async ({ coords }) => {
        const { status: locStatus, payload } = await fetchAPI<ILocation>(
          user.location
            ? `PUT /api/users/${user.id}/location`
            : `POST /api/users/${user.id}/location`,
          {
            long: coords.longitude,
            lat: coords.latitude,
          } as ILocationInput
        );
        // "Fake" user location to void multiple POST
        if (!user.location) {
          user.location = {
            id: 0,
            long: coords.longitude,
            lat: coords.latitude,
            updated_at: new Date().toISOString(),
          };
        }

        if (mymap) {
          const { status: usersStatus, payload: users } = await fetchAPI<
            IUser[]
          >("GET /api/users");
          if (usersStatus === StatusCodes.OK && typeof users !== "string") {
            for (const {
              id: user_id,
              status: user_status,
              location: user_location,
            } of users) {
              if (user_status === 1) {
                continue;
              }

              const latlng = {
                lat: user_location.lat,
                lng: user_location.long,
              };

              if (markers[user_id.toString()]) {
                markers[user_id.toString()].setLatLng(latlng);
              } else {
                const m = marker(latlng).addTo(mymap);
                if (user_id === user.id) {
                  m.bindPopup("Me").openPopup();
                }
                markers[user_id.toString()] = m;
              }
            }
          }
        }
      },
      (error) => {
        console.error("[LOCATION]", error);
      }
    );
  }
})();
