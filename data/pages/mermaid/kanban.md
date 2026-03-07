**[[:MERMAID|MERMAID:KANBAN]]**



----
**5 COLUMNS**

<mermaid>
kanban
  [[:TO-DO]]
    [Create Documentation]
    docs[Create Blog about the new diagram]
  [[:WIP]]
    id6[Create renderer so that it works in all cases. Add testing purposes. And some more just for the extra flare.]
  [[:NEXT]]
    id8[Design grammar]@{ assigned: 'ADMIN' }
  [[:ROCK]]
    id3[Weird flickering in Firefox]    
  [[:DONE]]
    id5[define getData]
    id2[Title of diagram is more than 100 chars when user duplicates diagram with 100 char]@{ ticket: 2036, priority: 'Very High'}
    id3[Update DB function]@{ ticket: 2037, assigned: ADMIN, priority: 'High' }

</mermaid>


----
**3 COLUMNS**

<mermaid>
kanban
[[:TO-DO]]
  #1[1. Create Documentation ]
  #2[2. Create Blog about the new diagram ]
[[:WIP]]
  #6[6. Create renderer so that it works in all cases. Add testing purposes. And some more just for the extra flare. ]
  #8[8. Design grammar]@{ assigned: 'ADMIN' }
[[:DONE]]
  #5[5. define getData ]
  #2[2. Title of diagram is more than 100 chars when user duplicates diagram with 100 char]@{ ticket: 2036, priority: 'Very High'}
  #3[3. Update DB function]@{ ticket: 2037, assigned: ADMIN, priority: 'High' }

</mermaid>



