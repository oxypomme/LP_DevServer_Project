import { StatusCodes } from "http-status-codes";
import { fetchAPI } from "./api";
import { ILocation, IUser } from "./types/responses";

navigator.geolocation.watchPosition(
  async ({ coords }) => {
    const { status, payload: user } = await fetchAPI<IUser>(
      "GET /api/users/me"
    );
    if (status !== StatusCodes.OK || typeof user === "string") {
      throw user;
    }

    const { status: locStatus, payload } = await fetchAPI<ILocation>(
      user.location
        ? `PUT /api/users/${user.id}/location`
        : `POST /api/users/${user.id}/location`,
      {
        long: coords.longitude,
        lat: coords.latitude,
      } as ILocationInput
    );
  },
  (error) => {
    console.error("[LOCATION]", error);
  }
);
