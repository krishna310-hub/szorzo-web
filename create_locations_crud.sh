#!/bin/bash
for model in Country State City; do
  lower=$(echo $model | tr '[:upper:]' '[:lower:]')
  plural="${lower}"
  if [ "$lower" == "city" ]; then
    plural="cities"
  elif [ "$lower" == "country" ]; then
    plural="countries"
  else
    plural="${lower}s"
  fi
  # We will just generate basic controllers and skip policies for now to avoid complexity, 
  # or comment out authorization in the controllers
done
