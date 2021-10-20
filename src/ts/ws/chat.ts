import { StatusCodes } from "http-status-codes";
import { fetchAPI } from "../api";
import { IMessage, IRelationList, IUser } from "../types/responses";

function onFriendClick(e: Event, { id }: IUser) {
  console.log("[MSG] [TODO] Show messages for", id);
}

function friendToHTML(user: IUser): HTMLElement {
  const container = document.createElement("div");
  container.classList.add("conversation");
  container.onclick = (e) => onFriendClick(e, user);

  const name = document.createElement("div");
  name.classList.add("name");
  name.innerText = user.username;
  container.appendChild(name);

  const lastMsg = document.createElement("div");
  lastMsg.classList.add("last-message", "side");
  lastMsg.innerText = "LAST MSG"; // TODO
  container.appendChild(lastMsg);

  return container;
}

function onMessageClick(e: Event, { id }: IMessage) {
  console.log("[MSG] [TODO] Clicked on", id);
}

function messageToHTML(message: IMessage): HTMLElement {
  const container = document.createElement("div");
  container.classList.add("message");
  container.onclick = (e) => onMessageClick(e, message);

  const contentEl = document.createElement("div");
  contentEl.classList.add("content");
  contentEl.innerText = message.content;

  const side = document.createElement("div");

  const name = document.createElement("div");
  name.classList.add("name");
  name.innerText = message.sender.username;
  side.appendChild(name);

  const sendAt = document.createElement("div");
  sendAt.classList.add("send-at");
  sendAt.innerText = new Date(message.created_at).toLocaleDateString();
  side.appendChild(sendAt);

  container.appendChild(side);

  return container;
}

(async () => {
  const messageForm = document.getElementById("message-form");
  if (messageForm) {
    const { status: myStatus, payload: currentUser } = await fetchAPI<IUser>(
      "GET /api/users/me"
    );
    if (myStatus !== StatusCodes.OK || typeof currentUser === "string") {
      console.error("[MSG]", currentUser);
      return;
    }
    // ? Merge two requests
    const { status, payload } = await fetchAPI<IRelationList>(
      `GET /api/users/${currentUser.id}/relations`
    );
    if (status === StatusCodes.OK && typeof payload !== "string") {
      const relationsContainer = document.querySelector(
        ".messages > .conversations"
      );
      if (relationsContainer) {
        for (const { target } of payload.relations) {
          relationsContainer.appendChild(friendToHTML(target));
        }
      }
    } else {
      //TODO: error management
    }
  }
})();
