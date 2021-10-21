import { StatusCodes } from "http-status-codes";
import { fetchAPI } from "../api";
import {
  IMessage,
  IRelationList,
  IUser,
  IMessageList,
} from "../types/responses";

let current_id: number;
let active_id: number;

async function onFriendClick(e: Event, { id }: IUser) {
  const messagesContainer = document.querySelector(
    ".messages .conversation-messages"
  );
  if (current_id && messagesContainer) {
    messagesContainer.innerHTML = "";
    const { status, payload } = await fetchAPI<IMessageList>(
      `GET /api/users/${current_id}/messages/${id}`
    );
    if (status === StatusCodes.OK && typeof payload !== "string") {
      //? Maybe not in the right order
      const messages = [...payload.outMessages, ...payload.inMessages].sort(
        (a, b) =>
          new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
      );
      const frag = document.createDocumentFragment();
      for (const message of messages) {
        frag.appendChild(
          messageToHTML(message, current_id === message.sender.id)
        );
      }
      messagesContainer.appendChild(frag);
    }
  }
}

function friendToHTML(user: IUser): HTMLElement {
  active_id = user.id;

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

function messageToHTML(message: IMessage, isSelf = false): HTMLElement {
  const container = document.createElement("div");
  container.classList.add("message");
  container.onclick = (e) => onMessageClick(e, message);
  if (isSelf) {
    container.classList.add("my-message");
  }

  const contentEl = document.createElement("div");
  contentEl.classList.add("content");
  contentEl.innerText = message.content;
  container.appendChild(contentEl);

  const side = document.createElement("div");
  side.classList.add("side");

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
    current_id = currentUser.id;
    const { status, payload } = await fetchAPI<IRelationList>(
      `GET /api/users/${currentUser.id}/relations`
    );
    if (status === StatusCodes.OK && typeof payload !== "string") {
      const relationsContainer = document.querySelector(
        ".messages > .conversations"
      );
      if (relationsContainer) {
        const frag = document.createDocumentFragment();
        for (const { target } of payload.relations) {
          frag.appendChild(friendToHTML(target));
        }
        relationsContainer.appendChild(frag);
      }
    } else {
      //TODO: error management
    }
  }
})();
