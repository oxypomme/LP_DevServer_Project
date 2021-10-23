import { StatusCodes } from "http-status-codes";
import { sendMessage } from ".";
import { fetchAPI } from "../api";
import { IMessage, IUser, IMessageList, IRelation } from "../types/responses";

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

function friendToHTML(
  user: IUser,
  isConnected: boolean,
  lastMessage?: IMessage
): HTMLElement {
  active_id = user.id;

  const container = document.createElement("div");
  container.classList.add("conversation");
  container.dataset.friend = user.id.toString();
  container.onclick = (e) => onFriendClick(e, user);
  if (isConnected) {
    container.classList.add("connected");
  }

  const name = document.createElement("div");
  name.classList.add("name");
  name.innerText = user.username;
  container.appendChild(name);

  const lastMsg = document.createElement("div");
  lastMsg.classList.add("last-message", "side");
  if (lastMessage) {
    lastMsg.innerText =
      (lastMessage.sender.id === current_id ? "You: " : "") +
      lastMessage.content;
  }
  container.appendChild(lastMsg);

  return container;
}

export function onFriendConnection(id: number): void {
  const relationsContainer = document.querySelector(
    ".messages > .conversations"
  );
  if (relationsContainer) {
    const friend = relationsContainer.querySelector(`[data-friend="${id}"]`);
    if (friend) {
      friend.classList.add("connected");
    }
  }
}

export function onFriendDisconnection(id: number): void {
  const relationsContainer = document.querySelector(
    ".messages > .conversations"
  );
  if (relationsContainer) {
    const friend = relationsContainer.querySelector(`[data-friend="${id}"]`);
    if (friend) {
      friend.classList.remove("connected");
    }
  }
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
  container.dataset.message = message.id.toString();

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

export function onNewMessage(message: IMessage): void {
  let target: number | undefined = undefined;
  if (message.target) {
    if (message.sender.id === current_id) {
      target = message.target.id;
    } else {
      target = message.sender.id;
    }
  } else {
    // TODO: groups
  }

  const friendLastMsg = document.querySelector(
    `.messages > .conversations [data-friend="${target}"] .last-message`
  );
  if (friendLastMsg) {
    friendLastMsg.innerHTML =
      (message.sender.id === current_id ? "You: " : "") + message.content;
  }

  if (active_id && active_id === target) {
    const messagesContainer = document.querySelector(
      ".messages .conversation-messages"
    );
    messagesContainer?.appendChild(
      messageToHTML(message, message.sender.id === current_id)
    );
  }
}

export function onMessageEdited(message: IMessage): void {
  // TODO: Message Edition
  throw new Error("NotImplementedError");
}

export function onMessageDeleted(message: IMessage): void {
  // TODO: Message Deletion
  throw new Error("NotImplementedError");
}

export function onFriendList(relations: IRelation[]): void {
  const relationsContainer = document.querySelector(
    ".messages > .conversations"
  );
  if (relationsContainer) {
    const frag = document.createDocumentFragment();
    for (const { target, isLogged, lastMessage } of relations) {
      frag.appendChild(friendToHTML(target, isLogged, lastMessage));
    }
    relationsContainer.appendChild(frag);
  }
}

(async () => {
  const messageForm = document.getElementById(
    "message-form"
  ) as HTMLFormElement;
  if (messageForm) {
    const { status: myStatus, payload: currentUser } = await fetchAPI<IUser>(
      "GET /api/users/me"
    );
    if (myStatus !== StatusCodes.OK || typeof currentUser === "string") {
      console.error("[MSG]", currentUser);
      return;
    }
    current_id = currentUser.id;

    if (!messageForm.onsubmit) {
      messageForm.onsubmit = (e) => {
        e.preventDefault();
        if (active_id) {
          const data = new FormData(messageForm);
          const message = data.get("new-message") as string;
          if (message) {
            sendMessage(message, "", active_id);
            messageForm.reset();
          }
        } else {
          //TODO: No selection
        }
      };
    }
  }
})();
