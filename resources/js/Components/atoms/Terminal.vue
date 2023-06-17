<template>
  <div ref="domElement" aria-hidden="true"/>
</template>

<script setup>

import {ref, onMounted, onUnmounted} from "vue";

let totalLines = 0;
let maxLines = 25;
let lineCount = 0;
let domElement = ref(null);
let timeOut;
let idx = 0;

const addLine = (line) => {
  totalLines++;

  if (lineCount === maxLines){
    document.getElementById(`line-${totalLines - maxLines}`).remove();
  } else {
    lineCount++;
  }

  const node = document.createElement("p");
  node.id = `line-${totalLines}`;
  node.innerHTML = line;
  domElement.value.appendChild(node);

  return 'line-' + lineCount;
}

const clearScreen = () => {
  domElement.innerHtml = '';
  lineCount = 0;
};

let dotsCallback = (endingText) => {
  return (config) => {
    let lineID = addLine(config.msg);
    let dots = ".";

    let incrementor = window.setInterval(() => {
      document.getElementById(lineID).innerHTML = `${config.msg} ${dots}`;
      if (dots.length >= 3){
        clearInterval(incrementor);
        const el = document.getElementById(lineID);
        el.innerHTML = `${config.msg} ${dots} ${endingText}`;
        el.querySelector('strong').innerText = '[OK]';
        next();
      }
      dots += ".";
    }, 200);
  }
};

const sequence = [
  {
    "msg": "Ore Bios (C) 2086 Mining Corp, Ltd.",
    "delay": 500,
  },
  {
    "msg": "BIOS Date 01/01/2086 16:13:29 Ver: 11.00.09",
    "delay": 0,
  },
  {
    "msg": "CPU: PPC(R) CPU RedCore @ 40Mhz",
    "delay": 500,
  },
  {
    "msg": "<strong class=\"selected\">Memory Test:</strong>",
    "delay": 250,
    "callback": (config) => {
      let lineID = addLine(config.msg);
      let counter = 0;
      let incrementor = window.setInterval(() => {
        document.getElementById(lineID).innerHTML = `${config.msg} ${counter} K`;

        counter++;
        if (counter === 640) {
          clearInterval(incrementor)
          document.getElementById(lineID).innerHTML = `${config.msg} ${counter} K OK`;
          next();
        }
      }, 10);
    }
  },
  {},
  {
    "delay": 300,
    "msg": "Booting from Hard Disk..."
  },
  {
    "delay": 300,
    "msg": "Starting ColonyOS v1.03",
  },
  {
    "delay": (200*3),
    "msg": "<strong>[...]</strong> Waiting for /dev to be fully populated",
    "callback": dotsCallback("done")
  },
  {
    "delay": (200*3),
    "msg": "<strong>[...]</strong> Detecting Network",
    "callback": dotsCallback("found")
  },
  {
    "delay": 300,
    "msg": "<strong class=\"selected\">[OK]</strong> Identifying Peripheral devices...done."
  },
  {
    "delay": 30,
    "msg": "<strong class=\"selected\">[OK]</strong> Harmonising Frequencies."
  },
  {
    "delay": 30,
    "msg": "<strong class=\"selected\">[OK]</strong> Identifying Lama Farmers"
  },
  {
    delay: (200*3),
    msg: "<strong>[...]</strong> Finding GOATs:",
    "callback": dotsCallback("found")
  },
  {
    "delay": 25,
    "msg": "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- found @cassidoo's discord, #FREETHEPLANT",
  },
  {
    "delay": 25,
    "msg": "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- found @weigert__, soil loaded",
  },
  {
    "delay": 25,
    "msg": "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- found Usborne Books",
  },
  {
    "delay": 30,
    "msg": "<strong class=\"selected\">[OK]</strong> Injecting Magic smoke...done."
  },
  {},
  {
    "delay": 300,
    "msg": "Mainframe Uplink Configured"
  },
  {},
  {
    "msg": "Starting OS/M"
  },
  {
    "msg": "System Specifications:",
    "delay": 250,
  },
  {
    "msg": "RAM: 640K",
    "delay": 250,
  },
  {
    "msg": "Hard Disk: 20 MB (BIOS Type 13)",
    "delay": 250,
  },
  {
    "msg": "Video Card: Enhanced Graphics Adapter",
    "delay": 250,
  },
  {
    "msg": "Floppy Drive A: 3.5\" 720k double-sided, double density",
    "delay": 250,
  },
  {
    "msg": "Floppy Drive B: Not installed",
    "delay": 250,
  },
  {},
  {
    "msg": "Installed Applications:",
    "delay": 250,
  },
  {
    "msg": "- RBASIC&nbsp;: Resource Harvesting SDK 1.1",
    "delay": 250,
  },
  {
    "msg": "- MCOMMS&nbsp;: Market Communications Systems",
    "delay": 250,
  },
  {
    "msg": "- REDIS&nbsp;&nbsp;: Storage Automation",
    "delay": 250,
  },
  {},
  {
    "msg": "C:\\><span class=\"carat\"></span>"
  },
];

const defaultSequence = {
  "msg": "&nbsp;",
  "delay": 1000,
  "callback": (config) => {
    addLine(config.msg);
    next();
  },
};

const run = () => {
  const current = sequence[idx];
  if (!current) return;

  let config = {
    "msg": current.msg || defaultSequence.msg,
    "delay": current.delay || defaultSequence.delay,
    "callback": current.callback || defaultSequence.callback,
  };

  try {
    timeOut = window.setTimeout(config.callback, config.delay, config);
  } catch (e) {
    return false;
  }
};

const next = () => {
  idx++;
  run();
}

onMounted(() => {
  domElement.value.innerHTML = '';
  run();
});

onUnmounted(() => window.clearTimeout(timeOut))
</script>

<style lang="postcss" scoped>
  div {
    font-family: monospace;
    text-shadow: 0 0.2rem 1rem #58412a;
  }

  .carat {
    animation: crt-carat 1000ms infinite;
    display: inline-block;
    height: 3px;
    width: 10px;
    margin: 0 4px -2px;
    background-color: #fb8337;
  }

  @keyframes crt-carat {
    50% {
      opacity: 0;
    }
    100% {
      opacity: 1;
    }
  }
</style>