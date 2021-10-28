import { StatusCodes } from "http-status-codes";
import { fetchAPI } from "./api";
import { IRelation, IUser } from "./types/responses";

(async () => {
  const searchSelect = document.getElementById(
    "select-users"
  ) as HTMLSelectElement;
  if (searchSelect) {
    const { payload: my, status: myStatus } = await fetchAPI<IUser>(
      "GET /api/users/me"
    );
    if (myStatus !== StatusCodes.OK || typeof my === "string") {
      throw my;
    }

    const { payload, status } = await fetchAPI<IUser[]>("GET /api/users");
    if (status === StatusCodes.OK && typeof payload !== "string") {
      const frag = document.createDocumentFragment();
      for (const user of payload) {
        if (user.id === my.id) {
          continue;
        }

        const option = document.createElement("option");
        option.value = user.id.toString();
        option.innerText = user.username;
        frag.appendChild(option);
      }
      searchSelect.appendChild(frag);
    } else {
      throw payload;
    }

    const searchButton = document.getElementById("users-button");
    if (searchButton && !searchButton.onclick) {
      searchButton.onclick = async (ev) => {
        ev.preventDefault();
        if (searchSelect) {
          const { payload: relation, status: inviteStatus } = await fetchAPI<
            IRelation,
            IRelationInput
          >(`POST /api/users/${my.id}/relations`, {
            target: parseInt(searchSelect.value),
          });
          if (inviteStatus === StatusCodes.OK && typeof relation !== "string") {
            // TODO: feedback
          } else {
            throw relation;
          }
        }
      };
    }
  }
})();
