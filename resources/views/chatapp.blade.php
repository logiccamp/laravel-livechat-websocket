<!DOCTYPE html>
<html>

<head>
  <title>Socket.IO chat</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.3/css/bootstrap.min.css" integrity="sha512-SbiR/eusphKoMVVXysTKG/7VseWii+Y3FdHrt0EpKgpToZeemhqHeZeLWLhJutz/2ut2Vw1uQEj2MbRF+TVBUA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    body {
      margin: 0;
      padding-bottom: 3rem;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    #form {
      background: rgba(0, 0, 0, 0.15);
      padding: 0.25rem;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      display: flex;
      height: 3rem;
      box-sizing: border-box;
      backdrop-filter: blur(10px);
    }

    #input {
      border: none;
      padding: 0 1rem;
      flex-grow: 1;
      border-radius: 2rem;
      margin: 0.25rem;
    }

    #input:focus {
      outline: none;
    }

    #form>button {
      background: #333;
      border: none;
      padding: 0 1rem;
      margin: 0.25rem;
      border-radius: 3px;
      outline: none;
      color: #fff;
    }

    #messages {
      list-style-type: none;
      margin: 0;
      padding: 0;
    }

    #messages>li {
      padding: 0.5rem 1rem;
    }

    #messages>li:nth-child(odd) {
      background: #efefef;
    }
  </style>
</head>

<body>
  <div class="" id="app">
    <div class="container">
      <div class="card-header">
        <h3>Chat App</h3>
      </div>
      <div>
        <div class="row flex-column my-3">
          <div class="col-lg-4">
            <label for="">Name</label>
            <input class="form-control" v-model="name" type="text" placeholder="Enter your name">
          </div>
          <div class="col-lg-4 mt-2 d-flex align-items-center">
            <button class="btn btn-danger" v-on:click="disconnect" v-if="connected == true">Disconnect</button>
            <button v-else v-on:click="connect" class="btn btn-primary">Connect</button>
          </div>
          <p v-if="state != null">Current state is @{{state}}</p>
        </div>
      </div>
      <ul id="messages">
        <li v-for="(msg, index) in messages" :key="index">
          <p class="m-0">
            @{{msg.time}} <strong> @{{msg.name}}</strong> : @{{msg.message}}
          </p>
        </li>
      </ul>
      <div class="card p-3" v-if="formError != ''">
        <h6>Message</h6>
        <p class="text-danger">@{{formError}}</p>
      </div>
      <div id="form">
        <input id="input" v-model="message" autocomplete="off" /><button v-on:click="sendMessage()">Send</button>
    </div>
    </div>
  </div>


  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js" integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://js.pusher.com/7.2.0/pusher.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.7.14/vue.min.js" integrity="sha512-BAMfk70VjqBkBIyo9UTRLl3TBJ3M0c6uyy2VMUrq370bWs7kchLNN9j1WiJQus9JAJVqcriIUX859JOm12LWtw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


  <script>
    new Vue({
      "el": "#app",
      "data": {
        connected: false,
        name: "Ogbon",
        formError : '',
        pusher : null,
        app : null,
        apps : '{!! json_encode($apps) !!}',
        host : "{{$host}}",
        port : "{{$port}}",
        authEndPoint : "{{$authEndPoint}}",
        logChannel : "{{$logChannel}}",
        messages: [
        //   {
        //   message: "Your message",
        //   name: "Emad",
        //   time: "2022"
        // }
      ],
        message : '',
        state : null
      },
      mounted() {
        this.app = this.apps[0] || null
      },
      methods: {
        connect() {
          this.pusher = new Pusher("staging", {
            wsHost : this.host,
            wsPort : this.port,
            wssPort : this.port,
            wsPath: this.app.path,
            disabledStats : true,
            forceTLS : false,
            authEndpoint: '/api/sockets/connect/',
            auth : {
              headers : {
                "X-CSRF-Token" : "{{csrf_token()}}",
                "X-App-ID" : this.app ? this.app.id : null,

              }
            },
            enabledTransports : ["ws", "flash"]
          })

          this.pusher.connection.bind("state_change", states =>{
            this.state = states.current
          })
          this.pusher.connection.bind("connected", ()=>{
            this.connected = true;
          })
          this.pusher.connection.bind("disconnected", ()=>{
            this.connected = false;
          })
          this.pusher.connection.bind("error", event=>{
            this.formError = "An error occur"
          })
          // this.connected = true
          this.subscribeAllChannels()
        },
        subscribeAllChannels(){
            [
              'api-message',
            ].forEach(channelName => this.subscribeAllChannel(channelName))
        },
        subscribeAllChannel(channelName){
          let inst = this;
          this.pusher.subscribe(this.logChannel+channelName).bind("log-message", (data)=>{
            if(data.type === 'api-message'){
              if(data.details.includes("SendMessageEvent")){
                let messageData = JSON.parse(data.data);
                if(messageData.chat_id == "{{$chat_id}}"){
                  let utcDate = new Date();
                  messageData.time = utcDate.toLocaleString();
                  inst.messages.push(messageData)
                }
              }
            }
          })
        },
        disconnect() {
          this.connected = false
        },
        sendMessage(){
            if(this.message === null || this.message === ""){
              this.formError = "Message cannot be empty"
            }else{
              $.post("/chat/send/{{$chat_id}}", {
                _token : '{{csrf_token()}}',
                name : this.name,
                msg : this.message
              }, function(d){

              }).fail(()=>{
                this.formError = "An error occur while sending ur message"
              })
            }
        }
      },
    })
  </script>
</body>

</html>